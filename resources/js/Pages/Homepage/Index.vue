<template>
	<!-- eslint-disable vue/no-parsing-error -->
	<div>
		<div class="header w-full bg-white mb-5 border-b-2">
			<div class="flex justify-between">
				<p class="text-4xl mx-6">
					<img src="/vendor/mailweb/icons/MailWebInline.png" width="75%">
				</p>
			</div>
		</div>
		<div class="mx-5">
			<div class="flex flex-wrap">
				<div class="w-1/5">
					<transition-group name="list" tag="div">
						<div v-for="email in emails" :key="email.id">
							<div
								:class="['card mx-2 mb-2 cursor-pointer', { 'bg-gray-200': email === selectedEmail }]"
								@click="selectedEmail = email"
							>
								<div class="card-header p-2">
									<span class="block">From < {{ getFromEmailAddress(email.from_email) }} ></span>
									<span class="block">To < {{ email.to_email }} ></span>
									<span class="block font-semibold">{{ email.subject }}</span>
									<span class="block">Sent: {{ parseDate(email.created_at) }}</span>
								</div>
							</div>
						</div>
					</transition-group>
				</div>
				<div v-if="selectedEmail !== null" :class="['h-screen', {'w-4/5': view === 'html' || view === 'markdown'}]">
					<!-- <div class="flex flex-wrap mb-2"> -->
					<div class="card w-full mx-5 mb-3">
						<button v-if="toolbar.LARGE_SCREEN" :class="[{ 'text-gray-300': view === 'xl' }, 'btn']" @click="view = 'xl'">
							<img src="/vendor/mailweb/icons/Monitor.svg" width="25">
						<!-- LG -->
						</button>
						<button v-if="toolbar.MEDIUM_SCREEN" :class="[{ 'text-gray-300': view === 'md' }, 'btn']" @click="view = 'md'">
							<img src="/vendor/mailweb/icons/Laptop.svg" width="30">
						<!-- MD -->
						</button>
						<button v-if="toolbar.SMALL_SCREEN" :class="[{ 'text-gray-300': view === 'sm' }, 'btn']" @click="view = 'sm'">
							<img src="/vendor/mailweb/icons/Phone.svg" width="20">
						<!-- SM -->
						</button>
						<button v-if="toolbar.HTML" :class="[{ 'text-gray-300': view === 'html' }, 'btn']" @click="view = 'html'">
							HTML Source
						</button>
						<button v-if="toolbar.MARKDOWN" :class="[{ 'text-gray-300': view === 'markdown' }, 'btn']" @click="view = 'markdown'">
							Markdown
						</button>
					</div>
					<!-- </div> -->
					<div :class="['card h-full mx-5 p-3 flex justify-center bg-white']">
						<iframe
							v-if="view === 'xl' || view === 'md' || view === 'sm'"
							:class="[widthClass, 'h-screen border-2']"
							:srcdoc="selectedEmail.body"
							frameborder="0"
						/>
						<pre v-else-if="view === 'html'" class="text-sm w-100"><code>{{ selectedEmail.body }}</code></pre>
						<pre v-else class="text-sm w-100">
                        {{ markdown }}
                    </pre>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import moment from 'moment';
import turndown from 'turndown';
import axios from 'axios';

export default {
    props: {
        toolbar: {
            type: Object,
        },
    },
    data() {
        return {
            selectedEmail: null,
            view: 'xl',
            emails: [],
        };
    },
    computed: {
        widthClass() {
            switch (this.view) {
            case 'md':
                return 'medium';
            case 'sm':
                return 'small';
            default:
                return 'large';
            }
        },
        markdown() {
            if (this.selectedEmail !== null) {
                let turndownService = new turndown();
                return turndownService.turndown(this.selectedEmail.body);
            }
            return '';
        },
        latestEmail(){
            let email = {};
            if(this.emails.length > 0){
                email = this.emails[0];
            }
            return email;
        },
    },
    mounted() {
        this.getEmails();
        window.addEventListener("focus",  this.getEmails);
    },
    methods: {
        getEmails() {
            const _this = this;
            let config = {};
            let sendingLatest = false;
            if(Object.prototype.hasOwnProperty.call(this.latestEmail, 'created_at')){
                sendingLatest = true;
                config.params = {
                    last_email_date: this.latestEmail.created_at,
                };
            }
        
            axios.get('/mailweb/emails', config).then(response => {
                if(sendingLatest){
                    if(response.data.length > 0){
                        this.emails = [...response.data, ..._this.emails];
                    }
                }else{
                    _this.emails = response.data;
                }
            });
        },
        parseDate(date) {
            return moment(date, 'YYYY/MM/DD HH:mm:ss').format('DD/MM/YYYY HH:mm:ss');
        },
        getFromEmailAddress(from_email) {
            if (from_email !== undefined && from_email !== null && from_email !== '') {
                return from_email;
            }
            return 'No from email found, please add one to your .env';
        },
    },
};
</script>

<style scoped>
pre {
    padding: 1em;
    overflow: auto;
    margin: 0.5em 0;
}
.small {
    width: 375px;
    height: 812px;
}
.medium {
    width: 1280px;
    height: 800px;
}
.large {
    width: 1920px;
    height: 1028px;
}
.list-enter-active,
.list-leave-active {
    transition: all 1s;
}
.list-enter, .list-leave-to /* .list-leave-active below version 2.1.8 */ {
    opacity: 0;
    transform: translateX(30px);
}
</style>
