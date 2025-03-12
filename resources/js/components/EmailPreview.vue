<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick, inject, defineEmits } from 'vue'
import { Eye, Code, Smartphone, Tablet, Monitor, Download, Copy, Share2, Check, Clock, QrCode, Trash2 } from 'lucide-vue-next'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Switch } from '@/components/ui/switch'
import EmailAttachments from './EmailAttachments.vue'
import type { EmailPreview, EmailAddress } from '@/types/email'

const emit = defineEmits(['delete-email'])

// Props with TypeScript interface
interface Props {
    email: EmailPreview
    isMobile: boolean
    isLoading?: boolean
}

const props = defineProps<Props>()

// Injected dependencies
const formatDate = inject<(dateString: string) => string>('formatDate')!

// Reactive state
const viewMode = ref<'html' | 'text' | 'raw'>('html')
const previewWidth = ref<'mobile' | 'tablet' | 'desktop'>('desktop')
const shareUrlCopied = ref(false)
const iframeRef = ref<HTMLIFrameElement | null>(null)
const showShareDialog = ref(false)
const isToggling = ref(false)
const shareUrl = ref('')
const qrCodeUrl = ref('')

// Computed properties
const previewStyle = computed(() => ({
    width: props.isMobile ? '100%' : getPreviewWidth(),
    maxWidth: '100%',
    transition: 'width 0.3s ease-in-out',
}))

// Utility functions
const formatEmailAddresses = (addresses: EmailAddress[]): string => {
    return addresses
        .map((addr) => (addr.name ? `${addr.name} <${addr.address}>` : addr.address))
        .join(', ')
}

const getPreviewWidth = (): string => {
    switch (previewWidth.value) {
        case 'mobile':
            return '375px'
        case 'tablet':
            return '768px'
        case 'desktop':
        default:
            return '100%'
    }
}

// Clipboard functions
const copyShareUrl = async (): Promise<void> => {
    if (!shareUrl.value) return

    try {
        if (navigator.clipboard) {
            await navigator.clipboard.writeText(shareUrl.value)
        } else {
            fallbackCopyTextToClipboard(shareUrl.value)
        }
        shareUrlCopied.value = true
        setTimeout(() => (shareUrlCopied.value = false), 2000)
    } catch (error) {
        console.error('Failed to copy to clipboard:', error)
        fallbackCopyTextToClipboard(shareUrl.value)
    }
}

const fallbackCopyTextToClipboard = (text: string): void => {
    const textArea = document.createElement('textarea')
    textArea.value = text
    textArea.style.position = 'fixed'
    textArea.style.top = '0'
    textArea.style.left = '0'
    document.body.appendChild(textArea)
    textArea.focus()
    textArea.select()

    try {
        document.execCommand('copy')
        shareUrlCopied.value = true
        setTimeout(() => (shareUrlCopied.value = false), 2000)
    } catch (error) {
        console.error('Fallback copy failed:', error)
    } finally {
        document.body.removeChild(textArea)
    }
}

// Action handlers
const handleDownload = (): void => {
    const content = viewMode.value === 'html' ? props.email.body_html : props.email.body_text
    const file = new Blob([content], { type: 'text/plain' })
    const element = document.createElement('a')
    element.href = URL.createObjectURL(file)
    element.download = `email-${props.email.id}.${viewMode.value === 'html' ? 'html' : 'txt'}`
    document.body.appendChild(element)
    element.click()
    document.body.removeChild(element)
}

const handleShare = (): void => {
    if (!props.email) return

    showShareDialog.value = true

    if (props.email.share_enabled && props.email.share_url && !shareUrl.value) {
        shareUrl.value = props.email.share_url
        qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(props.email.share_url)}`
    }
}

const toggleShareEnabled = async (): Promise<void> => {
    if (!props.email || isToggling.value) return

    isToggling.value = true
    try {
        const response = await axios.post<{ share_enabled: number; share_url?: string }>(
            `/mailweb/emails/${props.email.id}/toggle-share`
        )
        if (response.data.share_enabled) {
            shareUrl.value = response.data.share_url || ''
            qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(shareUrl.value)}`
        } else {
            shareUrl.value = ''
            qrCodeUrl.value = ''
        }
        props.email.share_enabled = response.data.share_enabled
    } catch (error) {
        console.error('Error toggling share status:', error)
    } finally {
        isToggling.value = false
    }
}

// Iframe management
const updateIframe = (): void => {
    nextTick(() => {
        if (iframeRef.value && props.email.body_html) {
            const iframeDoc = iframeRef.value.contentDocument || iframeRef.value.contentWindow?.document
            if (iframeDoc) {
                iframeDoc.open()
                iframeDoc.write(props.email.body_html)
                iframeDoc.close()
            }
        }
    })
}

const deleteEmail = async (): Promise<void> => {
    if (confirm('Are you sure you want to delete this email?')) {
        try {
            await axios.delete(`/mailweb/emails/${props.email.id}`)
            emit('delete-email', props.email.id)
        } catch (error) {
            console.error('Error deleting email:', error)
        }
    }
}

// Lifecycle hooks
watch(() => props.email, updateIframe, { immediate: true })
onMounted(updateIframe)
</script>

<template>
    <div class="flex flex-col h-[calc(100vh-57px)] lg:h-screen overflow-hidden">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 sm:p-4 border-b gap-2">
            <div class="overflow-hidden">
                <h2 class="text-lg font-semibold truncate">{{ email.subject }}</h2>
                <p class="text-sm text-muted-foreground">From: {{ formatEmailAddresses(email.from) }}</p>
                <p class="text-sm text-muted-foreground">To: {{ formatEmailAddresses(email.to) }}</p>
                <div class="flex items-center text-xs text-muted-foreground mt-1">
                    <Clock class="h-3 w-3 mr-1" />
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

                <Separator orientation="vertical" class="h-6 mx-1 hidden sm:block" />

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
        <div class="p-2 sm:p-4 border-b" v-if="email.attachments && email.attachments.length > 0">
            <EmailAttachments :attachments="email.attachments" />
        </div>

        <!-- Custom Tabs -->
        <div class="flex flex-col h-full">
            <!-- Tab Navigation -->
            <div class="border-b flex">
                <button class="px-4 py-2 flex items-center gap-2 text-sm font-medium transition-colors"
                    :class="viewMode === 'html' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = 'html'">
                    <Eye class="h-3 w-3 sm:h-4 sm:w-4" />
                    Preview
                </button>
                <button class="px-4 py-2 flex items-center gap-2 text-sm font-medium transition-colors"
                    :class="viewMode === 'text' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = 'text'">
                    <Code class="h-3 w-3 sm:h-4 sm:w-4" />
                    Text
                </button>
                <button v-if="!isMobile" class="px-4 py-2 flex items-center gap-2 text-sm font-medium transition-colors"
                    :class="viewMode === 'raw' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = 'raw'">
                    <Code class="h-3 w-3 sm:h-4 sm:w-4" />
                    Raw
                </button>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-hidden">
                <!-- HTML Preview -->
                <div v-show="viewMode === 'html'" class="h-full">
                    <div v-if="props.isLoading" class="h-full flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                            <p class="text-sm text-muted-foreground">Loading email content...</p>
                        </div>
                    </div>
                    <div v-else-if="!props.email.body_html" class="h-full flex items-center justify-center">
                        <p class="text-muted-foreground">No HTML content available</p>
                    </div>
                    <div v-else
                        class="h-full flex justify-center overflow-auto bg-gray-100 dark:bg-gray-900 transition-all duration-300"
                        :style="{ padding: isMobile || previewWidth === 'desktop' ? '0' : '1rem' }">
                        <div class="bg-white dark:bg-gray-800 h-full transition-all duration-300 shadow-sm"
                            :style="previewStyle">
                            <iframe ref="iframeRef" title="Email Preview" class="w-full h-full border-0"
                                sandbox="allow-same-origin allow-popups" />
                        </div>
                    </div>
                </div>

                <!-- Text View -->
                <div v-show="viewMode === 'text'" class="h-full p-4 overflow-auto">
                    <div v-if="props.isLoading" class="h-full flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                            <p class="text-sm text-muted-foreground">Loading email content...</p>
                        </div>
                    </div>
                    <pre v-else class="whitespace-pre-wrap font-mono text-sm">{{ email.body_text }}</pre>
                </div>

                <!-- Raw View -->
                <div v-show="viewMode === 'raw' && !isMobile" class="h-full p-4 overflow-auto">
                    <div v-if="props.isLoading" class="h-full flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                            <p class="text-sm text-muted-foreground">Loading email content...</p>
                        </div>
                    </div>
                    <pre v-else class="whitespace-pre-wrap font-mono text-sm">{{ JSON.stringify(email, null, 2) }}</pre>
                </div>
            </div>
        </div>

        <!-- Share Dialog -->
        <Dialog :open="showShareDialog" @update:open="showShareDialog = $event">
            <DialogContent
                class="max-w-[1000px] sm:max-w-[700px] max-h-[90vh] p-4 sm:p-6 flex flex-col justify-between">
                <div class="flex flex-col space-y-4 min-h-0 flex-1">
                    <DialogHeader>
                        <DialogTitle>Share Email</DialogTitle>
                    </DialogHeader>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <Share2 class="h-5 w-5 text-primary" />
                            <span class="font-medium text-sm">Email Sharing</span>
                        </div>
                        <Switch :model-value="email.share_enabled" @update:model-value="toggleShareEnabled"
                            :disabled="isToggling" />
                    </div>

                    <div v-if="email.share_enabled" class="flex flex-col space-y-4">
                        <div class="flex flex-col sm:flex-row gap-4 items-center">
                            <div class="flex-shrink-0 bg-white p-2 border rounded-lg shadow-sm">
                                <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="QR Code" class="w-48 h-48" />
                                <div v-else
                                    class="w-48 h-48 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded">
                                    <div class="animate-pulse">
                                        <QrCode class="h-8 w-8 text-gray-300" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 space-y-1 max-w-full text-sm">
                                <h3 class="font-medium">QR Code</h3>
                                <p class="text-gray-500 dark:text-gray-400">Scan this code with a mobile device to view
                                    the email.</p>
                                <p class="text-gray-400 dark:text-gray-500 text-xs sm:block hidden">
                                    Anyone with this code can access the content.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h3 class="font-medium text-sm">Share Link</h3>
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex-1 flex items-center border rounded-md overflow-hidden bg-gray-50 dark:bg-gray-800 min-w-0">
                                    <div class="flex-1 px-2 py-1 text-xs truncate">{{ shareUrl }}</div>
                                    <Button variant="ghost" size="sm" @click="copyShareUrl"
                                        class="h-full px-2 border-l hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0">
                                        <Check v-if="shareUrlCopied" class="h-3 w-3 text-green-500" />
                                        <Copy v-else class="h-3 w-3" />
                                        <span class="ml-1 text-xs">{{ shareUrlCopied ? 'Copied!' : 'Copy' }}</span>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-4 px-2 text-center">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-full mb-2">
                            <QrCode class="h-6 w-6 text-gray-400" />
                        </div>
                        <h3 class="text-sm font-medium mb-1">Enable sharing to continue</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs">
                            Toggle the switch above to generate a shareable link and QR code.
                        </p>
                    </div>
                </div>

                <DialogFooter class="mt-auto">
                    <Button variant="outline" @click="showShareDialog = false" class="text-xs">Close</Button>
                    <Button as="a" v-if="email.share_enabled && shareUrl" variant="default" :href="shareUrl"
                        target="_blank" class="ml-2 text-xs">
                        <Eye class="h-3 w-3 mr-1" />
                        Preview
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>