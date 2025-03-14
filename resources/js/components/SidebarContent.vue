<script setup lang="ts">
import { useMailwebConfig } from '@/composables/useMailwebConfig'
import { ref, onMounted, onUnmounted, watch, inject } from 'vue'
import { Search, Mail, RefreshCw, Settings, X, Play, Pause, ArrowLeft } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import toast from 'vue3-hot-toast'
import Github from '@/components/icons/Github.vue'
import AnimatedSendButton from './partials/AnimatedSendButton.vue'
import SettingsDialog from './partials/SettingsDialog.vue'
import axios from 'axios'

const props = defineProps<{
    searchQuery: string
    filters: Record<string, any>
    isMobile: boolean
}>()

const emit = defineEmits(['update:searchQuery', 'update:filters', 'close-sidebar', 'update:settings'])

const localSearchQuery = ref(props.searchQuery)
const fetchEmails = inject('fetchEmails') as () => void
const isRefreshing = ref<boolean>(false)
const isSending = ref<boolean>(false)
const isPolling = ref<boolean>(false)
const pollingInterval = ref<number | null>(null)
const paginationTriggered = inject('paginationTriggered', null) as any
const isPollingActive = inject('isPollingActive', true) as boolean
const showSettingsDialog = ref<boolean>(false)

const settings = ref({
    paginationAmount: 25,
    dateFormat: 'days-ago'
})

const { getReturnConfig, isDeleteAllEnabled: checkDeleteAllEnabled, isSendSampleButtonEnabled: checkSendSampleButtonEnabled } = useMailwebConfig()
const returnConfig = ref({ appName: '', appUrl: '' })
const isSendSampleButtonEnabled = ref<boolean>(false)
const isDeleteAllEnabled = ref<boolean>(false)

onMounted(() => {
    isDeleteAllEnabled.value = checkDeleteAllEnabled()
    returnConfig.value = getReturnConfig()
    isSendSampleButtonEnabled.value = checkSendSampleButtonEnabled()
    loadSettings()
    if (isPollingActive) startPolling()
    if (paginationTriggered) {
        watch(paginationTriggered, (triggered) => {
            if (triggered && isPolling.value) {
                stopPolling()
                toast.success('Auto-refresh has been disabled while you\'re browsing. You can re-enable it in the bottom left corner')
            }
        })
    }
})

watch(localSearchQuery, (newValue) => emit('update:searchQuery', newValue))
watch(() => props.searchQuery, (newValue) => {
    if (newValue !== localSearchQuery.value) localSearchQuery.value = newValue
})

const POLLING_INTERVAL_MS = 5000

const startPolling = (): void => {
    if (pollingInterval.value !== null) return
    if (paginationTriggered) paginationTriggered.value = false
    handleRefresh()
    pollingInterval.value = window.setInterval(() => {
        if (document.visibilityState === 'visible') handleRefresh()
    }, POLLING_INTERVAL_MS)
    isPolling.value = true
    if (isPollingActive) isPollingActive.value = true
}

const stopPolling = (): void => {
    if (pollingInterval.value !== null) {
        window.clearInterval(pollingInterval.value)
        pollingInterval.value = null
    }
    isPolling.value = false
    if (isPollingActive) isPollingActive.value = false
}

const togglePolling = (): void => isPolling.value ? stopPolling() : startPolling()

const handleRefresh = (): void => {
    isRefreshing.value = true
    if (fetchEmails) fetchEmails()
    setTimeout(() => isRefreshing.value = false, 1000)
}

const loadSettings = (): void => {
    const savedSettings = localStorage.getItem('mailweb-settings')
    if (savedSettings) {
        try {
            settings.value = { ...settings.value, ...JSON.parse(savedSettings) }
            emit('update:settings', settings.value)
        } catch (e) {
            console.error('Failed to parse settings from localStorage:', e)
        }
    }
}

const sendTestEmail = async (): Promise<void> => {
    isSending.value = true
    try {
        const response = await axios.get('/mailweb/send-test-email', {
            headers: { 'Accept': 'application/json' }
        })
        toast.success(response.status === 200 ? 'Test email sent successfully' : 'Failed to send test email')
    } catch (error) {
        toast.error('Failed to send test email')
        console.error('Error sending test email:', error)
    } finally {
        isSending.value = false
    }
}

onUnmounted(() => stopPolling())
</script>

<template>
    <div class="flex flex-col h-full">
        <div class="p-4 border-b">
            <div class="flex items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2">
                    <Mail class="h-5 w-5 text-primary" />
                    <h1 class="text-xl font-bold">Mailweb</h1>
                </div>
                <Button v-if="isMobile" variant="ghost" size="icon" @click="$emit('close-sidebar')">
                    <X class="h-5 w-5" />
                    <span class="sr-only">Close</span>
                </Button>
            </div>
            <div class="relative">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input placeholder="Search emails..." class="pl-8" v-model="localSearchQuery" />
            </div>
        </div>

        <div class="mt-auto">
            <div class="p-4 border-t">
                <a :href="returnConfig.appUrl || '/'" class="text-primary text-xs hover:underline">
                    <ArrowLeft class="inline-block w-4 h-4" /> Return to {{ returnConfig.appName || 'App' }}
                </a>
            </div>

            <div class="p-4 border-t">
                <div class="flex items-center justify-between">
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button :variant="isPolling ? 'default' : 'ghost'" size="icon" @click="togglePolling"
                                    class="relative">
                                    <Play v-if="!isPolling" class="h-4 w-4" />
                                    <Pause v-else-if="!isRefreshing" class="h-4 w-4" />
                                    <RefreshCw v-if="isRefreshing" class="h-4 w-4 animate-spin absolute" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>{{ isPolling ? 'Stop' : 'Start' }} Auto-Refresh</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                    <template v-if="!isMobile">
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="ghost" size="icon" @click="showSettingsDialog = true">
                                        <Settings class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>Settings</TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider v-if="isSendSampleButtonEnabled">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <AnimatedSendButton :disabled="isSending" @click="sendTestEmail" />
                                </TooltipTrigger>
                                <TooltipContent>Send Test Email</TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button as="a" href="https://github.com/appoly/mail-web" target="_blank"
                                        rel="noopener noreferrer" variant="ghost" size="icon">
                                        <Github class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>GitHub</TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <SettingsDialog :show-settings-dialog="showSettingsDialog" :is-delete-all-enabled="isDeleteAllEnabled"
        :initial-settings="settings" @update:show-settings-dialog="showSettingsDialog = $event"
        @update:settings="settings = $event; emit('update:settings', $event)" />
</template>