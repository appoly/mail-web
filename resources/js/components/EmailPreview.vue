<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick, inject, defineEmits } from 'vue'
import { Eye, Code, Smartphone, Tablet, Monitor, Download, Share2, Clock, Trash2 } from 'lucide-vue-next'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import EmailAttachments from './EmailAttachments.vue'
import DeleteEmailDialog from './partials/DeleteEmailDialog.vue'
import ShareEmailDialog from './partials/ShareEmailDialog.vue'
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
const iframeRef = ref<HTMLIFrameElement | null>(null)
const showShareDialog = ref(false)
const showDeleteDialog = ref(false)

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

const deleteEmail = (): void => {
    showDeleteDialog.value = true
}

const handleDeleteConfirm = async (): Promise<void> => {
    try {
        await axios.delete(`/mailweb/emails/${props.email.id}`)
        emit('delete-email', props.email.id)
        showDeleteDialog.value = false
    } catch (error) {
        console.error('Error deleting email:', error)
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
                    <pre v-else class="whitespace-pre-wrap font-mono text-sm">{{ email.body_html }}</pre>
                </div>
            </div>
        </div>

        <!-- Dialog Components -->
        <DeleteEmailDialog 
            :open="showDeleteDialog" 
            @update:open="showDeleteDialog = $event"
            @confirm="handleDeleteConfirm" 
        />

        <ShareEmailDialog 
            :open="showShareDialog" 
            :email="email"
            @update:open="showShareDialog = $event" 
        />
    </div>
</template>