<template>
    <div class="mx-5 w-full">
        <div class="flex flex-wrap">
            <div class="header w-full my-5">
                <p class="text-4xl">Mail Web</p>
                <hr />
            </div>

            <div class="w-1/4">
                <div v-for="email in emails" :key="email.id">
                    <div
                        :class="['card mx-2 mb-2 cursor-pointer', { 'bg-gray-200': email === selectedEmail }]"
                        @click="selectedEmail = email"
                    >
                        <div class="card-header p-2">
                            <span class="block">From <{{ getFromEmailAddress(email.from_email) }}></span>
                            <span class="block">To <{{ email.to_email }}></span>
                            <span class="block font-semibold">{{ email.subject }}</span>
                            <span class="block">Sent: {{ parseDate(email.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-3/4 h-screen" v-if="selectedEmail !== null">
                <!-- <div class="flex flex-wrap mb-2"> -->
                <div class="card w-full mx-5 mb-3">
                    <button :class="[{ 'text-gray-300': view === 'xl' }, 'btn']" @click="view = 'xl'">XL</button>
                    <button :class="[{ 'text-gray-300': view === 'md' }, 'btn']" @click="view = 'md'">MD</button>
                    <button :class="[{ 'text-gray-300': view === 'sm' }, 'btn']" @click="view = 'sm'">SM</button>
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
                        :class="[widthClass, 'h-screen']"
                        :srcdoc="selectedEmail.body"
                        frameborder="0"
                    ></iframe>
                    <pre class="text-sm" v-else-if="view === 'html'"><code>{{ selectedEmail.body }}</code></pre>
                    <pre class="text-sm" v-else>
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

export default {
    props: {
        emails: {
            type: Array,
            default: null,
        },
    },
    computed: {
        widthClass() {
            switch (this.view) {
                case 'md':
                    return 'w-1/2';
                    break;
                case 'sm':
                    return 'w-1/4';
                    break;
                default:
                    return 'w-full';
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
    data() {
        return {
            selectedEmail: null,
            view: 'xl',
        };
    },
    methods: {
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
</style>
