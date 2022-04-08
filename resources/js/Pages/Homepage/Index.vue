<template>
	<div class="content h-100">
		<div class="header">
			<div class="row">
				<div class="col-3">
					<div class="px-4">
						<div class="d-flex">
							<h1>MailWeb</h1>
							<img class="ml-4" src="/vendor/mailweb/icons/logo.svg" alt="">
						</div>
					</div>
				</div>
				<div class="col">
					<div class="d-flex">
						<button :class="['btn font-smaller mx-2', {'selected' : view == 'xl'}]" @click="view = 'xl'">
							<div class="d-flex align-items-center">
								<img src="/vendor/mailweb/icons/desktop.svg" alt="">
								<span class="px-2 align-self-center">
									Desktop
								</span>
							</div>
						</button>
						<button :class="['btn font-smaller mx-2', {'selected' : view == 'md'}]" @click="view = 'md'">
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/laptop.svg" alt="">
								<span class="px-2 align-self-center">
									Laptop
								</span>
							</div>
						</button>
						<button :class="['btn font-smaller mx-2', {'selected' : view == 'sm'}]" @click="view = 'sm'">
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/mobile.svg" alt="">
								<span class="px-2 align-self-center">
									Mobile
								</span>
							</div>
						</button>
						<button :class="['btn font-smaller mx-2', {'selected' : view == 'html'}]" @click="view = 'html'">
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/html.svg" alt="">
								<span class="px-2 align-self-center">
									HTML Source
								</span>
							</div>
						</button>
						<button :class="['btn font-smaller mx-2', {'selected': view == 'markdown'}]" @click="view = 'markdown'">
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/markdown.svg" alt="">
								<span class="px-2 align-self-center">
									Markdown
								</span>
							</div>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="row h-100 overflow-auto">
			<div class="col-sm-4 col-md-4 col-lg-3 col-xl-3 bg-white">
				<div class="py-4 px-4 ">
					<input
						v-model="search"
						type="text"
						placeholder="Search via Subject, To or From"
						class="form-control font-smaller mb-4"
					>

					<transition-group name="list" tag="div">
						<div v-for="email in filteredEmails" :key="email.id">
							<div
								:class="['card font-smaller email-card my-3', {'selected': selectedEmail == email}]"
								@click="selectedEmail = email"
							>
								<div class="card-body">
									<span class="d-block font-weight-bold">{{ email.subject }}</span>

									<span class="fw-lighter d-block text-muted">From: {{ getFromEmailAddress(email.from_email) }}</span>
									<span class="fw-lighter d-block text-muted">To: {{ email.to_email }}</span>
									<span class="fw-lighter d-block text-muted">Sent: {{ parseDate(email.created_at) }}</span>
								</div>
							</div>
						</div>
					</transition-group>
				</div>
			</div>
			<div class="col-sm-8 col-md-8 col-lg-9 col-xl-9 unset-padding-left">
				<div v-if="selectedEmail" class="email-content h-100">
					<iframe
						v-if="(view === 'xl' || view === 'md' || view === 'sm')"
						:class="widthClass"
						:srcdoc="selectedEmail.body"
						frameborder="0"
					/>
					<pre v-else-if="view === 'html'" class="w-100 h-100"><code>{{ selectedEmail.body }}</code></pre>
					<pre v-else class="text-sm w-100">
                            {{ markdown }}
                        </pre>
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
            search: '',
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
        filteredEmails() {
            return this.emails.filter(email => {
                return email.subject.toLowerCase().includes(this.search.toLowerCase()) ||
                    email.to_email.toLowerCase().includes(this.search.toLowerCase()) ||
                    email.from_email.toLowerCase().includes(this.search.toLowerCase());
            });
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
                    _this.selectedEmail = _this.emails[0];
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

<style lang="scss" scoped>
pre, code {
    padding: 1em;
    overflow: auto;
    font-family: monospace;
    white-space: pre;
    margin: 1em 0;
    height: 35rem;
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