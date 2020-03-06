<template>
    <div class="mx-5 w-full">
        <div class="flex flex-wrap">
            <div class="header w-full my-5">
                <p class="text-4xl">Mail Web</p>
                <hr />
            </div>

            <div class="w-1/4">
                <div v-for="email in emails" :key="email.id">
                    <div :class="['card mx-2 mb-2 cursor-pointer', {'bg-gray-200':email === selectedEmail}]" @click="selectedEmail = email">
                        <div class="card-header p-2">
                            <span class="block">From <{{email.from_email}}></span>
                            <span class="block">To <{{email.to_email}}></span>
                            <span class="block font-semibold">{{ email.subject }}</span>
                            <span class="block">Sent: {{ parseDate(email.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-3/4 h-screen" v-if="selectedEmail !== null">
                <!-- <div class="flex flex-wrap mb-2"> -->
                <div class="card w-full mx-5 mb-3">
                    <button :class="[{'text-gray-300':responsiveSize === 'xl'},'btn']" @click="responsiveSize = 'xl'">XL</button>
                    <button :class="[{'text-gray-300':responsiveSize === 'md'},'btn']" @click="responsiveSize = 'md'">MD</button>
                    <button :class="[{'text-gray-300':responsiveSize === 'sm'},'btn']" @click="responsiveSize = 'sm'">SM</button>
                </div>
                <!-- </div> -->
                <div class="card h-full mx-5 flex justify-center bg-gray-200">
                    <iframe :class="[widthClass, 'h-screen']" :srcdoc="selectedEmail.body" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    props: {
        emails: {
            type: Array,
            default: null
        }
    },
    computed: {
        widthClass() {
            switch (this.responsiveSize) {
                case "xl":
                    return "w-full";
                    break;
                case "md":
                    return "w-1/2";
                    break;
                case "sm":
                    return "w-1/4";
                    break;
            }
        }
    },
    data() {
        return {
            selectedEmail: null,
            responsiveSize: "xl"
        };
    },
    methods: {
        parseDate(date) {
            return moment(date, 'YYYY/MM/DD').format('DD/MM/YYYY')
        }
    },
};
</script>
