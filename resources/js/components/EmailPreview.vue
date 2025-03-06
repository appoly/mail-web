<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { Eye, Code, Smartphone, Tablet, Monitor, Download, Copy, ExternalLink, Check } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Separator } from '@/components/ui/separator'
import { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import { EmailPreview as Email } from '@/types/email'

const props = defineProps<{
    email: Email
    isMobile: boolean
}>()

const viewMode = ref<'html' | 'text' | 'raw'>('html')
const previewWidth = ref<'mobile' | 'tablet' | 'desktop'>('desktop')
const copied = ref(false)
const iframeRef = ref<HTMLIFrameElement | null>(null)

const getPreviewWidth = () => {
    switch (previewWidth.value) {
        case 'mobile':
            return '375px'
        case 'tablet':
            return '768px'
        case 'desktop':
            return '100%'
        default:
            return '100%'
    }
}

const previewStyle = computed(() => ({
    width: props.isMobile ? '100%' : getPreviewWidth(),
    maxWidth: '100%',
    transition: 'width 0.3s ease-in-out'
}))

const handleCopyHtml = () => {
    if (props.email.content) {
        navigator.clipboard.writeText(props.email.content)
        copied.value = true
        setTimeout(() => (copied.value = false), 2000)
    }
}

const handleDownload = () => {
    const element = document.createElement('a')
    const file = new Blob([viewMode.value === 'html' ? props.email.content : ''], { type: 'text/plain' })
    element.href = URL.createObjectURL(file)
    element.download = `email-${props.email.id}.${viewMode.value === 'html' ? 'html' : 'txt'}`
    document.body.appendChild(element)
    element.click()
    document.body.removeChild(element)
}

const updateIframe = () => {
    // wait 2 seconds
    // Using nextTick to ensure DOM is updated before accessing the iframe
    nextTick(() => {
        console.log('Iframe reference:', iframeRef.value)
        if (iframeRef.value && viewMode.value === 'html') {
            const iframeDoc = iframeRef.value.contentDocument || iframeRef.value.contentWindow?.document
            if (iframeDoc) {
                iframeDoc.open()
                console.log('Email content:', props.email.content)
                iframeDoc.write(props.email.content)
                iframeDoc.close()
            }
        }
    })
}

watch([() => props.email, viewMode], updateIframe, { immediate: true })
onMounted(updateIframe)
</script>

<template>
    <div class="flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 sm:p-4 border-b gap-2">
            <div class="overflow-hidden">
                <h2 class="text-lg font-semibold truncate">{{ email.subject }}</h2>
                <p class="text-sm text-muted-foreground truncate">
                    From: {{ email.from }} • To: {{ email.to }}
                </p>
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

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="previewWidth = 'tablet'">
                                <Tablet :class="['h-4 w-4', previewWidth === 'tablet' ? 'text-primary' : '']" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Tablet View</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="previewWidth = 'desktop'">
                                <Monitor :class="['h-4 w-4', previewWidth === 'desktop' ? 'text-primary' : '']" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Desktop View</TooltipContent>
                    </Tooltip>

                    <Separator orientation="vertical" class="h-6 mx-1 hidden sm:block" />

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="handleCopyHtml">
                                <Check v-if="copied" class="h-4 w-4 text-green-500" />
                                <Copy v-else class="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>{{ copied ? 'Copied!' : 'Copy HTML' }}</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" @click="handleDownload">
                                <Download class="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Download</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" as-child>
                                <a href="#" target="_blank" rel="noopener noreferrer">
                                    <ExternalLink class="h-4 w-4" />
                                </a>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Open in new tab</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </div>

        <!-- Tabs -->
        <Tabs v-model:active="viewMode">
            <TabsList class="h-9">
                <TabsTrigger value="html">
                    <div class="flex gap-2">
                        <Eye class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                        Preview
                    </div>
                </TabsTrigger>
                <TabsTrigger value="text">
                    <div class="flex gap-2">
                        <Code class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                        Text
                    </div>
                </TabsTrigger>
                <TabsTrigger v-if="!isMobile" value="raw">
                    <div class="flex gap-2">
                        <Code class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                        Raw
                    </div>
                </TabsTrigger>
            </TabsList>

            <TabsContent value="html" class="h-full m-0 p-0">
                <div class="h-full flex justify-center overflow-auto bg-gray-100 dark:bg-gray-900 transition-all duration-300"
                    :style="{ padding: isMobile || previewWidth === 'desktop' ? '0' : '1rem' }">
                    <div class="bg-white dark:bg-gray-800 h-full transition-all duration-300 shadow-sm"
                        :style="previewStyle">
                        <iframe ref="iframeRef" title="Email Preview" class="w-full h-full border-0"
                            sandbox="allow-same-origin" />
                    </div>
                </div>
            </TabsContent>

            <TabsContent value="text" class="h-full m-0 p-4 overflow-auto">
                <pre class="whitespace-pre-wrap font-mono text-sm">{{ email.content }}</pre>
            </TabsContent>

            <TabsContent v-if="!isMobile" value="raw" class="h-full m-0 p-4 overflow-auto">
                <pre class="whitespace-pre-wrap font-mono text-sm">{{ JSON.stringify(email, null, 2) }}</pre>
            </TabsContent>
        </Tabs>
    </div>
</template>
