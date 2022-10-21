<template>
	<div class="content h-100">
		<div class="header">
			<div class="row">
				<div class="col-3">
					<div class="px-4">
						<div class="d-flex">
							<h1>MailWeb</h1>
							<img class="ml-4" src="/vendor/mailweb/icons/logo.svg">
						</div>
					</div>
				</div>
				<div class="col">
					<div class="d-flex">
						<button :class="['btn font-smaller mx-2', { 'selected': view == 'xl' }]" @click="view = 'xl'">
							<div class="d-flex align-items-center">
								<img src="/vendor/mailweb/icons/desktop.svg">
								<span class="px-2 align-self-center">
									Desktop
								</span>
							</div>
						</button>
						<button :class="['btn font-smaller mx-2', { 'selected': view == 'md' }]" @click="view = 'md'">
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/laptop.svg">
								<span class="px-2 align-self-center">
									Laptop
								</span>
							</div>
						</button>
						<button :class="['btn font-smaller mx-2', { 'selected': view == 'sm' }]" @click="view = 'sm'">
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/mobile.svg">
								<span class="px-2 align-self-center">
									Mobile
								</span>
							</div>
						</button>
						<button
							:class="['btn font-smaller mx-2', { 'selected': view == 'html' }]"
							@click="view = 'html'"
						>
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/html.svg">
								<span class="px-2 align-self-center">
									HTML Source
								</span>
							</div>
						</button>
						<button
							:class="['btn font-smaller mx-2', { 'selected': view == 'markdown' }]"
							@click="view = 'markdown'"
						>
							<div class="d-flex align-items-center ">
								<img src="/vendor/mailweb/icons/markdown.svg">
								<span class="px-2 align-self-center">
									Markdown
								</span>
							</div>
						</button>
						<div class="ml-auto">
							<button class="btn font-smaller mx-2" @click="back">
								<div class="d-flex align-items-center ">
									<!-- markdown image rotated 45 deg  -->
									<img class="rotate-90" src="/vendor/mailweb/icons/markdown.svg">
									<span class="px-2 align-self-center">
										Back
									</span>
								</div>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row h-100 overflow-auto">
			<div class="col-sm-4 col-md-4 col-lg-3 col-xl-3 bg-white">
				<div class="py-4 px-4 ">
					<div class="form-group">
						<input
							v-model="search"
							type="text"
							placeholder="Search via Subject, To or From"
							class="form-control font-smaller mb-4"
						>
					</div>
					<div class="d-flex align-items-center">
						<div class="form-group w-100 mr-1">
							<label class="font-smaller">From</label>
							<input v-model="dates.from" type="date" class="form-control font-smaller">
						</div>
						<div class="form-group w-100 ml-1">
							<label class="font-smaller">To</label>
							<input v-model="dates.to" type="date" class="form-control font-smaller">
						</div>
					</div>

					<transition-group name="list" tag="div">
						<div v-for="email in filteredSearchEmails" :key="email.id">
							<div
								:class="['card font-smaller email-card my-3 cursor-pointer', { 'selected': selectedEmail == email }]"
								@click="changeEmail(email)"
							>
								<div class="card-body">
									<span class="d-block font-weight-bold">
										{{ email.subject }}
									</span>
									<span class="fw-lighter d-block text-muted">
										From: {{ getFromEmailAddress(email.from_email) }}
									</span>
									<span class="fw-lighter d-block text-muted">
										To: {{ getToEmailAddress(email) }}
									</span>
									<span class="fw-lighter d-block text-muted">
										Sent: {{ parseDate(email.created_at) }}
									</span>
									<span v-if="email.attachments.length > 0" class="fw-lighter d-block text-muted">
										Attachments: {{ email.attachments.length }}
									</span>
								</div>
							</div>
						</div>
					</transition-group>
				</div>
			</div>
			<div v-if="filteredSearchEmails.length > 0" class="col-sm-8 col-md-8 col-lg-9 col-xl-9 unset-padding-left">
				<div class="email-content h-100 d-flex justify-content-center">
					<iframe
						v-if="(view === 'xl' || view === 'md' || view === 'sm')"
						ref="emailFrame"
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
            dates: {
                from: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                to: moment().format('YYYY-MM-DD'),
            },
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
        latestEmail() {
            let email = {};
            if (this.emails.length > 0) {
                email = this.emails[0];
            }
            return email;
        },
        filteredSearchEmails() {
            return this.emails.filter(email => {
				
                return email.subject.toLowerCase().includes(this.search.toLowerCase()) ||
					this.getToEmailAddress(email).toLowerCase().includes(this.search.toLowerCase()) ||
					email.from_email.toLowerCase().includes(this.search.toLowerCase());
            });
        },
    },
    watch: {
        dates: {
            handler() {
                this.getEmails();
            },
            deep: true,
        },
        selectedEmail(newVal, oldVal) {
            // Don't allow selectedEmail to be null
            if(newVal == null || newVal == undefined){
                this.selectedEmail = oldVal;
            }
        },
    },
    mounted() {
        this.getEmails();
        window.addEventListener("focus", this.getEmails);
    },
    methods: {
        getEmails() {
            const _this = this;
            let config = this.dates;

            axios.get('/mailweb/emails', {
                params: config,
            }).then(response => {
                _this.emails = response.data;
                if (_this.selectedEmail === null) {
                    _this.selectedEmail = _this.latestEmail;
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
        getToEmailAddress(email){
            // to email is an array
            return email.to_emails.join(', ');
        },
        back() {
            //get previous page url
            let url = document.referrer;
            //redirect to previous page
            window.location.href = url;
        },
        changeEmail(email) {
            this.selectedEmail = email;
        },
    },
};
</script>

<style lang="scss" scoped>
pre,
code {
	padding: 1em;
	overflow: auto;
	font-family: monospace;
	white-space: pre;
	margin: 1em 0;
	height: 35rem;
}

.email-content {
	iframe {
		transition: all 0.5s ease;
		opacity: 1;

		&.small {
			width: 375px;
			height: 812px;
		}

		&.medium {
			width: 1280px;
			height: 800px;
		}

		&.large {
			width: 1920px;
			height: 1028px;
		}

		&.hidden {
			opacity: 0;
		}
	}
}


.list-enter-active,
.list-leave-active {
	transition: all 1s;
}

.list-enter,
.list-leave-to

/* .list-leave-active below version 2.1.8 */
	{
	opacity: 0;
	transform: translateX(30px);
}

.rotate-90 {
	transform: rotate(90deg);
}

.ml-1 {
	margin-left: 1rem;
}

.mr-1 {
	margin-right: 1rem;
}

.ml-auto {
	margin-left: auto;
}
</style>