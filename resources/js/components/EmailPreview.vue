<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { EmailAddress, EmailPreview } from '@/types/email';
import axios from 'axios';
import { Clock, Code, Download, Eye, Monitor, Share2, Smartphone, Tablet, Trash2 } from 'lucide-vue-next';
import { computed, defineEmits, inject, nextTick, onMounted, ref, watch } from 'vue';
import EmailAttachments from './EmailAttachments.vue';
import DeleteEmailDialog from './partials/DeleteEmailDialog.vue';
import ShareEmailDialog from './partials/ShareEmailDialog.vue';

const emit = defineEmits(['delete-email']);

// Props with TypeScript interface
interface Props {
    email: EmailPreview;
    isMobile: boolean;
    isLoading?: boolean;
}

const props = defineProps<Props>();

// Injected dependencies
const formatDate = inject<(dateString: string) => string>('formatDate')!;

// Reactive state
const viewMode = ref<'html' | 'text' | 'raw'>('html');
const previewWidth = ref<'mobile' | 'tablet' | 'desktop'>('desktop');
const iframeRef = ref<HTMLIFrameElement | null>(null);
const showShareDialog = ref(false);
const showDeleteDialog = ref(false);

// Computed properties
const previewStyle = computed(() => ({
    width: props.isMobile ? '100%' : getPreviewWidth(),
    maxWidth: '100%',
    transition: 'width 0.3s ease-in-out',
}));

// Utility functions
const formatEmailAddresses = (addresses?: EmailAddress[]): string => {
    if (!addresses || addresses.length === 0) return 'None';
    return addresses.map((addr) => {
        if (!addr) return 'Unknown';
        return addr.name ? `${addr.name} <${addr.address}>` : addr.address;
    }).join(', ');
};

const getPreviewWidth = (): string => {
    switch (previewWidth.value) {
        case 'mobile':
            return '375px';
        case 'tablet':
            return '768px';
        case 'desktop':
        default:
            return '100%';
    }
};

// Action handlers
const handleDownload = (): void => {
    const content = viewMode.value === 'html' ? props.email.body_html : props.email.body_text;
    const file = new Blob([content], { type: 'text/plain' });
    const element = document.createElement('a');
    element.href = URL.createObjectURL(file);
    element.download = `email-${props.email.id}.${viewMode.value === 'html' ? 'html' : 'txt'}`;
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
};

const handleShare = (): void => {
    if (!props.email) return;
    showShareDialog.value = true;
};

// Iframe management
const updateIframe = (): void => {
    nextTick(() => {
        if (iframeRef.value && props.email.body_html) {
            const iframeDoc = iframeRef.value.contentDocument || iframeRef.value.contentWindow?.document;
            if (iframeDoc) {
                const scriptContent = `
                    setTimeout(function() {
                        const links = document.querySelectorAll("a:not([target])");
                        links.forEach(link => {
                            link.setAttribute("target", "_blank");
                        });
                    }, 1000);
                `;
                
                iframeDoc.open();
                iframeDoc.write(props.email.body_html);
                iframeDoc.close();
                
                const script = iframeDoc.createElement('script');
                script.textContent = scriptContent;
                iframeDoc.body.appendChild(script);
            }
        }
    });
};

const deleteEmail = (): void => {
    showDeleteDialog.value = true;
};

const handleDeleteConfirm = async (): Promise<void> => {
    try {
        await axios.delete(`/mailweb/emails/${props.email.id}`);
        emit('delete-email', props.email.id);
        showDeleteDialog.value = false;
    } catch (error) {
        console.error('Error deleting email:', error);
    }
};

// Lifecycle hooks
watch(() => props.email, updateIframe, { immediate: true });
onMounted(updateIframe);
</script>

<template>
    <div class="flex h-[calc(100vh-57px)] flex-col overflow-hidden lg:h-screen">
        <!-- Header -->
        <div class="flex flex-col justify-between gap-2 border-b p-2 sm:flex-row sm:items-center sm:p-4">
            <div class="overflow-hidden">
                <h2 class="truncate text-lg font-semibold">{{ email.subject }}</h2>
                <p class="text-sm text-muted-foreground">From: {{ formatEmailAddresses(email.from) }}</p>
                <div>
                    <p v-if="email.to && email.to.length > 0" class="text-sm text-muted-foreground">To: {{ formatEmailAddresses(email.to) }}</p>
                    <p v-else class="text-sm italic text-destructive font-medium">To: No recipients</p>
                </div>
                <p v-if="email.cc && email.cc.length > 0" class="text-sm text-muted-foreground">CC: {{ formatEmailAddresses(email.cc) }}</p>
                <p v-if="email.bcc && email.bcc.length > 0" class="text-sm text-muted-foreground">BCC: {{ formatEmailAddresses(email.bcc) }}</p>
                <div class="mt-1 flex items-center text-xs text-muted-foreground">
                    <Clock class="mr-1 h-3 w-3" />
                    <span>{{ formatDate(email.created_at) }}</span>
                </div>
            </div>

            <div v-if="!isMobile" class="flex items-center gap-1 sm:gap-2">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="previewWidth = 'mobile'">
                                <Smartphone :class="['h-4 w-4', previewWidth === 'mobile' ? 'text-primary' : '']" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Mobile View</TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="previewWidth = 'tablet'">
                                <Tablet :class="['h-4 w-4', previewWidth === 'tablet' ? 'text-primary' : '']" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Tablet View</TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="previewWidth = 'desktop'">
                                <Monitor :class="['h-4 w-4', previewWidth === 'desktop' ? 'text-primary' : '']" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Desktop View</TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <Separator orientation="vertical" class="mx-1 hidden h-6 sm:block" />

                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="handleDownload">
                                <Download class="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Download</TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="handleShare">
                                <Share2 class="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Share Email</TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="destructive" size="icon" @click="deleteEmail">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Delete Email</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </div>
        <div class="border-b p-2 sm:p-4" v-if="email.attachments && email.attachments.length > 0">
            <EmailAttachments :attachments="email.attachments" />
        </div>

        <!-- Custom Tabs -->
        <div class="flex h-full flex-col">
            <!-- Tab Navigation -->
            <div class="flex border-b">
                <button
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="viewMode === 'html' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = 'html'"
                >
                    <Eye class="h-3 w-3 sm:h-4 sm:w-4" />
                    Preview
                </button>
                <button
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="viewMode === 'text' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = 'text'"
                >
                    <Code class="h-3 w-3 sm:h-4 sm:w-4" />
                    Text
                </button>
                <button
                    v-if="!isMobile"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="viewMode === 'raw' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = 'raw'"
                >
                    <Code class="h-3 w-3 sm:h-4 sm:w-4" />
                    Raw
                </button>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-hidden">
                <!-- HTML Preview -->
                <div v-show="viewMode === 'html'" class="h-full">
                    <div v-if="props.isLoading" class="flex h-full items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="mb-2 h-8 w-8 animate-spin rounded-full border-b-2 border-primary"></div>
                            <p class="text-sm text-muted-foreground">Loading email content...</p>
                        </div>
                    </div>
                    <div v-else-if="!props.email.body_html" class="flex h-full items-center justify-center">
                        <p class="text-muted-foreground">No HTML content available</p>
                    </div>
                    <div
                        v-else
                        class="flex h-full justify-center overflow-auto bg-gray-100 transition-all duration-300 dark:bg-gray-900"
                        :style="{ padding: isMobile || previewWidth === 'desktop' ? '0' : '1rem' }"
                    >
                        <div class="h-full bg-white shadow-sm transition-all duration-300 dark:bg-gray-800" :style="previewStyle">
                            <iframe ref="iframeRef" title="Email Preview" class="h-full w-full border-0" sandbox="allow-same-origin allow-popups allow-scripts" />
                        </div>
                    </div>
                </div>

                <!-- Text View -->
                <div v-show="viewMode === 'text'" class="h-full overflow-auto p-4">
                    <div v-if="props.isLoading" class="flex h-full items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="mb-2 h-8 w-8 animate-spin rounded-full border-b-2 border-primary"></div>
                            <p class="text-sm text-muted-foreground">Loading email content...</p>
                        </div>
                    </div>
                    <pre v-else class="whitespace-pre-wrap font-mono text-sm">{{ email.body_text }}</pre>
                </div>

                <!-- Raw View -->
                <div v-show="viewMode === 'raw' && !isMobile" class="h-full overflow-auto p-4">
                    <div v-if="props.isLoading" class="flex h-full items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="mb-2 h-8 w-8 animate-spin rounded-full border-b-2 border-primary"></div>
                            <p class="text-sm text-muted-foreground">Loading email content...</p>
                        </div>
                    </div>
                    <pre v-else class="whitespace-pre-wrap font-mono text-sm">{{ email.body_html }}</pre>
                </div>
            </div>
        </div>

        <!-- Dialog Components -->
        <DeleteEmailDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event" @confirm="handleDeleteConfirm" />

        <ShareEmailDialog :open="showShareDialog" :email="email" @update:open="showShareDialog = $event" />
    </div>
</template>
