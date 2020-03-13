<template>
	<!-- eslint-disable vue/no-parsing-error -->
	<div class="mx-5 w-full">
		<div class="flex flex-wrap">
			<div class="header w-full my-5">
				<p class="text-4xl">
					Mail Web
				</p>
				<hr>
			</div>

			<div class="w-1/4">
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
			</div>
			<div v-if="selectedEmail !== null" class="w-3/4 h-screen">
				<!-- <div class="flex flex-wrap mb-2"> -->
				<div class="card w-full mx-5 mb-3">
					<button :class="[{ 'text-gray-300': view === 'xl' }, 'btn']" @click="view = 'xl'">
						<!-- <img src="/vendor/mailweb/icons/Monitor.svg" width="35"> -->
						LG
					</button>
					<button :class="[{ 'text-gray-300': view === 'md' }, 'btn']" @click="view = 'md'">
						<!-- <img src="/vendor/mailweb/icons/Laptop.svg" width="35"> -->
						MD
					</button>
					<button :class="[{ 'text-gray-300': view === 'sm' }, 'btn']" @click="view = 'sm'">
						<!-- <img src="/vendor/mailweb/icons/iPhone.svg" width="35"> -->
						SM
					</button>
					<button :class="[{ 'text-gray-300': view === 'html' }, 'btn']" @click="view = 'html'">
						HTML Source
					</button>
					<button :class="[{ 'text-gray-300': view === 'markdown' }, 'btn']" @click="view = 'markdown'">
						Markdown
					</button>
				</div>
				<!-- </div> -->
				<div class="card h-full mx-5 flex justify-center bg-gray-200">
					<iframe
						v-if="view === 'xl' || view === 'md' || view === 'sm'"
						:class="[widthClass, 'h-screen border-2']"
						:srcdoc="selectedEmail.body"
						frameborder="0"
					/>
					<pre v-else-if="view === 'html'" class="text-sm"><code>{{ selectedEmail.body }}</code></pre>
					<pre v-else class="text-sm">
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
    },
    mounted() {
        this.getEmails();
        window.setInterval(() => {
            this.getEmails();
        }, 5000);
    },
    methods: {
        getEmails() {
            const _this = this;
            axios.get('/mailweb/emails').then(response => {
                _this.emails = response.data;
            });
        },
        parseDate(date) {
            return moment(date, 'YYYY/MM/DD').format('DD/MM/YYYY');
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
.small{
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
</style>
