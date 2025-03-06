<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Menu } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import Sidebar from '@/components/Sidebar.vue';
import EmailList from '@/components/EmailList.vue';
import EmailPreview from '@/components/EmailPreview.vue';
import EmailDetails from '@/components/EmailDetails.vue';
import SlidingPanel from '@/components/SlidingPanel.vue';
import { Email } from '@/types/email';

// Sample data for emails
const emails: Email[] = [
    {
        id: 1,
        subject: 'Welcome to MailWeb!',
        from: 'support@mailweb.dev',
        to: 'user@example.com',
        date: '2025-06-10T09:30:00',
        preview: 'Thank you for installing MailWeb. Here are some tips to get started...',
        read: true,
        attachments: [],
        content: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #3b82f6;">Welcome to MailWeb!</h1>
        <p>Thank you for installing our package. We're excited to help you streamline your email development and debugging process.</p>
        <!-- Rest of the content same as React version -->
      </div>
    `
    },
    {
        id: 2,
        subject: 'News from MailWeb',
        from: 'news@mailweb.dev',
        to: 'user@example.com',
        date: '2025-06-11T09:30:00',
        preview: 'We released a new version of MailWeb with some exciting changes. Check it out!',
        read: true,
        attachments: [],
        content: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #3b82f6;">News from MailWeb</h1>
        <p>We're excited to announce that we released a new version of MailWeb with some exciting changes. Check it out!</p>
      </div>
    `
    },
    {
        id: 3,
        subject: 'Important: Update your MailWeb package',
        from: 'support@mailweb.dev',
        to: 'user@example.com',
        date: '2025-06-11T09:30:00',
        preview: 'A security issue was discovered in MailWeb. Update your package now to stay safe!',
        read: false,
        attachments: [],
        content: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #3b82f6;">Important: Update your MailWeb package</h1>
        <p>A security issue was discovered in MailWeb. Update your package now to stay safe!</p>
      </div>
    `
    },
    {
        id: 4,
        subject: 'Your MailWeb account has been created',
        from: 'support@mailweb.dev',
        to: 'user@example.com',
        date: '2025-06-10T09:30:00',
        preview: 'Your account has been created. Please verify your email address to unlock all features.',
        read: false,
        attachments: [],
        content: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #3b82f6;">Your MailWeb account has been created</h1>
        <p>Your account has been created. Please verify your email address to unlock all features.</p>
      </div>
    `
    },
];

// Reactive state
const selectedEmail = ref<Email | null>(null);
const searchQuery = ref<string>('');
const sidebarOpen = ref<boolean>(false);
const isMobile = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const error = ref<string>('');
const filters = ref<Record<string, any>>({});

// Computed filtered emails
const filteredEmails = computed<Email[]>(() =>
    emails.filter(email =>
        email.subject.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        email.from.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        email.to.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (email.preview?.toLowerCase().includes(searchQuery.value.toLowerCase()) ?? false)
    )
);

// Methods
const handleShare = (): void => {
    if (selectedEmail.value) {
        navigator.clipboard.writeText(`https://mailweb.example.com/share/${selectedEmail.value.id}`);
        // toast({
        //   title: "Share link copied!",
        //   description: "Email preview link has been copied to clipboard",
        // });
    }
};

const handleDelete = (): void => {
    // toast({
    //   title: "Email deleted",
    //   description: "The email has been moved to trash",
    // });
};

const checkMobile = (): void => {
    isMobile.value = window.innerWidth < 1024;
};

// Lifecycle hooks
onMounted((): void => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="flex flex-col h-screen overflow-hidden lg:flex-row">
        <div v-if="isMobile" class="flex items-center p-4 border-b">
            <Button variant="ghost" size="icon" @click="sidebarOpen = true" class="mr-2">
                <Menu class="h-5 w-5" />
                <span class="sr-only">Toggle menu</span>
            </Button>
            <div class="flex items-center">
                <h1 class="text-xl font-bold">Mailweb</h1>
            </div>
        </div>

        <Sidebar v-model:searchQuery="searchQuery" v-model:filters="filters" v-model:isOpen="sidebarOpen"
            :isMobile="isMobile" />

        <div class="flex flex-col flex-1 h-[calc(100vh-57px)] lg:h-screen overflow-hidden">
            <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">
                <template v-if="isMobile">
                    <SlidingPanel>
                        <EmailList :emails="filteredEmails" v-model:selectedEmail="selectedEmail" :isLoading="isLoading"
                            :error="error" :isMobile="isMobile" />
                        <template #preview>
                            <div v-if="selectedEmail" class="h-full overflow-y-auto">
                                <EmailPreview :email="selectedEmail" :isMobile="isMobile" @share="handleShare"
                                    @delete="handleDelete" />
                                <!-- <EmailDetails :email="selectedEmail" :isMobile="isMobile" /> -->
                            </div>
                            <div v-else class="flex items-center justify-center h-full p-6 text-center bg-muted/30">
                                <div>
                                    <h3 class="text-xl font-medium">No email selected</h3>
                                    <p class="text-muted-foreground">Select an email from the list to preview it</p>
                                </div>
                            </div>
                        </template>
                    </SlidingPanel>
                </template>
                <template v-else>
                    <EmailList :emails="filteredEmails" v-model:selectedEmail="selectedEmail" :isLoading="isLoading"
                        :error="error" :isMobile="isMobile" />
                    <template v-if="selectedEmail">
                        <div class="flex flex-col flex-1 overflow-hidden">
                            <EmailPreview :email="selectedEmail" :isMobile="isMobile" @share="handleShare"
                                @delete="handleDelete" />
                            <!-- <EmailDetails :email="selectedEmail" :isMobile="isMobile" /> -->
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex items-center justify-center flex-1 p-6 text-center bg-muted/30">
                            <div>
                                <h3 class="text-xl font-medium">No email selected</h3>
                                <p class="text-muted-foreground">Select an email from the list to preview it</p>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>