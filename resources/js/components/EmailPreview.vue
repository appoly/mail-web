<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { EmailAddress, EmailPreview } from '@/types/email';
import axios from 'axios';
import {
    Clock,
    Code,
    Download,
    Eye,
    FileText,
    Loader2,
    Monitor,
    Share2,
    Smartphone,
    Tablet,
    Trash2,
} from 'lucide-vue-next';
import { computed, inject, nextTick, ref, watch } from 'vue';
import EmailAttachments from './EmailAttachments.vue';
import DeleteEmailDialog from './partials/DeleteEmailDialog.vue';
import ShareEmailDialog from './partials/ShareEmailDialog.vue';

const emit = defineEmits(['delete-email']);

interface Props {
    email: EmailPreview;
    isMobile: boolean;
    isLoading?: boolean;
}

const props = defineProps<Props>();

const formatDate = inject<(dateString: string) => string>('formatDate')!;

// Reactive state
const viewMode = ref<'html' | 'text' | 'raw'>('html');
const previewWidth = ref<'mobile' | 'tablet' | 'desktop'>('desktop');
const iframeRef = ref<HTMLIFrameElement | null>(null);
const showShareDialog = ref(false);
const showDeleteDialog = ref(false);

// Computed
const previewStyle = computed(() => ({
    width: props.isMobile ? '100%' : getPreviewWidth(),
    maxWidth: '100%',
    transition: 'width 0.3s ease-in-out',
}));

// Utility functions
const formatEmailAddresses = (addresses?: EmailAddress[]): string => {
    if (!addresses || addresses.length === 0) return 'None';
    return addresses
        .map((addr) => {
            if (!addr) return 'Unknown';
            return addr.name ? `${addr.name} <${addr.address}>` : addr.address;
        })
        .join(', ');
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
    URL.revokeObjectURL(element.href);
};

const handleShare = (): void => {
    if (!props.email) return;
    showShareDialog.value = true;
};

const deleteEmail = (): void => {
    showDeleteDialog.value = true;
};

// Iframe management
const updateIframe = (): void => {
    nextTick(() => {
        if (iframeRef.value && props.email.body_html) {
            const iframeDoc = iframeRef.value.contentDocument || iframeRef.value.contentWindow?.document;
            if (iframeDoc) {
                const parser = new DOMParser();
                const emailDoc = parser.parseFromString(props.email.body_html, 'text/html');

                emailDoc.querySelectorAll('a').forEach((link) => {
                    link.setAttribute('target', '_blank');
                    link.setAttribute('rel', 'noopener noreferrer');
                });

                iframeDoc.open();
                iframeDoc.write(emailDoc.documentElement.outerHTML);
                iframeDoc.close();
            }
        }
    });
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

watch(() => props.email, updateIframe, { immediate: true });
</script>

<template>
    <div class="flex h-[calc(100vh-53px)] flex-col overflow-hidden lg:h-screen">
        <!-- Header -->
        <div class="border-b px-5 py-3 sm:px-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <!-- Subject -->
                    <h2 class="truncate text-lg font-semibold leading-tight tracking-tight">{{ email.subject }}</h2>

                    <!-- Address lines — always visible -->
                    <div class="mt-2 space-y-0.5 text-sm">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-muted-foreground shrink-0 text-xs">From:</span>
                            <span class="truncate">{{ formatEmailAddresses(email.from) }}</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-muted-foreground shrink-0 text-xs">To:</span>
                            <span v-if="email.to && email.to.length > 0" class="truncate">{{ formatEmailAddresses(email.to) }}</span>
                            <span v-else class="text-destructive italic">No recipients</span>
                        </div>
                        <div v-if="email.cc && email.cc.length > 0" class="flex items-baseline gap-1.5">
                            <span class="text-muted-foreground shrink-0 text-xs">Cc:</span>
                            <span class="truncate">{{ formatEmailAddresses(email.cc) }}</span>
                        </div>
                        <div v-if="email.bcc && email.bcc.length > 0" class="flex items-baseline gap-1.5">
                            <span class="text-muted-foreground shrink-0 text-xs">Bcc:</span>
                            <span class="truncate">{{ formatEmailAddresses(email.bcc) }}</span>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="text-muted-foreground mt-1.5 flex items-center gap-1.5 text-xs">
                        <Clock class="h-3 w-3" />
                        <span>{{ formatDate(email.created_at) }}</span>
                    </div>
                </div>

                <!-- Desktop Toolbar -->
                <div v-if="!isMobile" class="flex items-center gap-1 rounded-md border p-1">
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button
                                    variant="ghost" size="icon"
                                    @click="previewWidth = 'mobile'"
                                    class="h-7 w-7"
                                    :class="previewWidth === 'mobile' ? 'bg-accent text-primary' : 'text-muted-foreground'"
                                >
                                    <Smartphone class="h-3.5 w-3.5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Mobile view</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button
                                    variant="ghost" size="icon"
                                    @click="previewWidth = 'tablet'"
                                    class="h-7 w-7"
                                    :class="previewWidth === 'tablet' ? 'bg-accent text-primary' : 'text-muted-foreground'"
                                >
                                    <Tablet class="h-3.5 w-3.5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Tablet view</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button
                                    variant="ghost" size="icon"
                                    @click="previewWidth = 'desktop'"
                                    class="h-7 w-7"
                                    :class="previewWidth === 'desktop' ? 'bg-accent text-primary' : 'text-muted-foreground'"
                                >
                                    <Monitor class="h-3.5 w-3.5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Desktop view</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <Separator orientation="vertical" class="mx-0.5 h-5" />

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="ghost" size="icon" @click="handleDownload" class="h-7 w-7 text-muted-foreground hover:text-foreground">
                                    <Download class="h-3.5 w-3.5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Download</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="ghost" size="icon" @click="handleShare" class="h-7 w-7 text-muted-foreground hover:text-foreground">
                                    <Share2 class="h-3.5 w-3.5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Share</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <Separator orientation="vertical" class="mx-0.5 h-5" />

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="ghost" size="icon" @click="deleteEmail" class="h-7 w-7 text-muted-foreground hover:text-destructive">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Delete</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>

                <!-- Mobile Actions -->
                <div v-else class="flex shrink-0 items-center gap-1">
                    <Button variant="ghost" size="icon" @click="handleShare" class="h-8 w-8 text-muted-foreground">
                        <Share2 class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" @click="handleDownload" class="h-8 w-8 text-muted-foreground">
                        <Download class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" @click="deleteEmail" class="h-8 w-8 text-muted-foreground hover:text-destructive">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Attachments -->
        <div class="border-b px-5 py-3 sm:px-6" v-if="email.attachments && email.attachments.length > 0">
            <EmailAttachments :attachments="email.attachments" />
        </div>

        <!-- Tabs + Content -->
        <Tabs v-model="viewMode" class="flex h-full flex-col">
            <TabsList class="h-auto w-full justify-start gap-0 rounded-none border-b bg-transparent p-0 px-2">
                <TabsTrigger
                    value="html"
                    class="inline-flex items-center gap-1.5 rounded-none border-b-2 border-transparent bg-transparent px-4 py-2.5 text-sm font-medium text-muted-foreground shadow-none data-[state=active]:border-b-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                >
                    <Eye class="h-3.5 w-3.5" />
                    Preview
                </TabsTrigger>
                <TabsTrigger
                    value="text"
                    class="inline-flex items-center gap-1.5 rounded-none border-b-2 border-transparent bg-transparent px-4 py-2.5 text-sm font-medium text-muted-foreground shadow-none data-[state=active]:border-b-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                >
                    <FileText class="h-3.5 w-3.5" />
                    Text
                </TabsTrigger>
                <TabsTrigger
                    v-if="!isMobile"
                    value="raw"
                    class="inline-flex items-center gap-1.5 rounded-none border-b-2 border-transparent bg-transparent px-4 py-2.5 text-sm font-medium text-muted-foreground shadow-none data-[state=active]:border-b-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                >
                    <Code class="h-3.5 w-3.5" />
                    Source
                </TabsTrigger>
            </TabsList>

            <TabsContent value="html" class="mt-0 flex-1 overflow-hidden data-[state=inactive]:hidden">
                <div class="h-full">
                    <div v-if="props.isLoading" class="flex h-full items-center justify-center">
                        <div class="flex flex-col items-center gap-3">
                            <Loader2 class="text-primary h-6 w-6 animate-spin" />
                            <p class="text-muted-foreground text-sm">Loading content...</p>
                        </div>
                    </div>
                    <div v-else-if="!props.email.body_html" class="flex h-full items-center justify-center">
                        <p class="text-muted-foreground text-sm">No HTML content available</p>
                    </div>
                    <div
                        v-else
                        class="flex h-full justify-center overflow-auto bg-muted/30 transition-all duration-300"
                        :style="{ padding: isMobile || previewWidth === 'desktop' ? '0' : '1.5rem' }"
                    >
                        <div class="h-full bg-white shadow-sm transition-all duration-300 dark:bg-gray-800" :style="previewStyle">
                            <iframe
                                ref="iframeRef"
                                title="Email Preview"
                                class="h-full w-full border-0"
                                sandbox="allow-same-origin allow-popups"
                            />
                        </div>
                    </div>
                </div>
            </TabsContent>

            <TabsContent value="text" class="mt-0 flex-1 overflow-hidden data-[state=inactive]:hidden">
                <div class="h-full overflow-auto p-5">
                    <div v-if="props.isLoading" class="flex h-full items-center justify-center">
                        <div class="flex flex-col items-center gap-3">
                            <Loader2 class="text-primary h-6 w-6 animate-spin" />
                            <p class="text-muted-foreground text-sm">Loading content...</p>
                        </div>
                    </div>
                    <pre v-else class="font-mono text-sm leading-relaxed whitespace-pre-wrap">{{ email.body_text }}</pre>
                </div>
            </TabsContent>

            <TabsContent v-if="!isMobile" value="raw" class="mt-0 flex-1 overflow-hidden data-[state=inactive]:hidden">
                <div class="h-full overflow-auto p-5">
                    <div v-if="props.isLoading" class="flex h-full items-center justify-center">
                        <div class="flex flex-col items-center gap-3">
                            <Loader2 class="text-primary h-6 w-6 animate-spin" />
                            <p class="text-muted-foreground text-sm">Loading content...</p>
                        </div>
                    </div>
                    <pre v-else class="bg-muted/50 rounded-lg p-4 font-mono text-sm leading-relaxed whitespace-pre-wrap">{{ email.body_html }}</pre>
                </div>
            </TabsContent>
        </Tabs>

        <!-- Dialog Components -->
        <DeleteEmailDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event" @confirm="handleDeleteConfirm" />
        <ShareEmailDialog :open="showShareDialog" :email="email" @update:open="showShareDialog = $event" />
    </div>
</template>
