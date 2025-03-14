<script setup lang="ts">
// Declare mailwebConfig on window object
declare global {
    interface Window {
        mailwebConfig?: {
            deleteAllEnabled: boolean,
            sendSampleButton: boolean,
            return: {
                appName: string,
                appUrl: string
            }
        }
    }
}

import { ref, onMounted, onUnmounted, watch, inject, nextTick } from 'vue'
import { Search, Mail, Filter, RefreshCw, Settings, X, Play, Pause, ArrowLeft, Trash2, AlertCircle } from 'lucide-vue-next'

import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, } from '@/components/ui/dialog'
import toast from 'vue3-hot-toast'
import Github from '@/components/icons/Github.vue'
import AnimatedSendButton from './partials/AnimatedSendButton.vue'
import axios from 'axios'

const props = defineProps<{
    searchQuery: string
    filters: Record<string, any>
    isMobile: boolean
}>()

const localSearchQuery = ref(props.searchQuery)

watch(localSearchQuery, (newValue) => {
    emit('update:searchQuery', newValue)
})

watch(() => props.searchQuery, (newValue) => {
    if (newValue !== localSearchQuery.value) {
        localSearchQuery.value = newValue
    }
})

const emit = defineEmits(['update:searchQuery', 'update:filters', 'close-sidebar', 'update:settings'])

const fetchEmails = inject('fetchEmails') as () => void

const isRefreshing = ref<boolean>(false)
const isSending = ref<boolean>(false)
const isDeleting = ref<boolean>(false)
const isPolling = ref<boolean>(false)
const pollingInterval = ref<number | null>(null)

const paginationTriggered = inject('paginationTriggered', null) as any
const isPollingActive = inject('isPollingActive', true) as boolean

const showSettingsDialog = ref<boolean>(false)
const showDeleteAllDialog = ref<boolean>(false)
const showDeleteAllDisabledDialog = ref<boolean>(false)
const paginationOptions: number[] = [10, 25, 50, 100]
const dateFormatOptions = [
    { value: 'timestamp', label: 'Timestamp' },
    { value: 'uk', label: 'UK (DD/MM/YYYY)' },
    { value: 'us', label: 'US (MM/DD/YYYY)' },
    { value: 'days-ago', label: 'X Days ago' }
]
const settings: Record<string, any> = ref({
    paginationAmount: 25,
    dateFormat: 'days-ago'
})

const returnConfig = ref({
    appName: '',
    appUrl: ''
})

const isSendSampleButtonEnabled = ref<boolean>(false)
const isDeleteAllEnabled = ref<boolean>(false)

onMounted(() => {
    if (window.mailwebConfig && typeof window.mailwebConfig.deleteAllEnabled === 'boolean') {
        isDeleteAllEnabled.value = window.mailwebConfig.deleteAllEnabled
    }
    if (window.mailwebConfig && window.mailwebConfig.return) {
        returnConfig.value = window.mailwebConfig.return
    }
    if (window.mailwebConfig && typeof window.mailwebConfig.sendSampleButton === 'boolean') {
        isSendSampleButtonEnabled.value = window.mailwebConfig.sendSampleButton
    }
})

const POLLING_INTERVAL_MS = 5000

const startPolling = (): void => {
    if (pollingInterval.value !== null) return
    if (paginationTriggered) {
        paginationTriggered.value = false
    }
    handleRefresh()
    pollingInterval.value = window.setInterval(() => {
        if (document.visibilityState === 'visible') {
            handleRefresh()
        }
    }, POLLING_INTERVAL_MS)
    isPolling.value = true
    if (isPollingActive) {
        isPollingActive.value = true
    }
}

const stopPolling = (): void => {
    if (pollingInterval.value !== null) {
        window.clearInterval(pollingInterval.value)
        pollingInterval.value = null
    }
    isPolling.value = false
    if (isPollingActive) {
        isPollingActive.value = false
    }
}

const togglePolling = (): void => {
    if (isPolling.value) {
        stopPolling()
    } else {
        startPolling()
    }
}

const handleRefresh = (): void => {
    isRefreshing.value = true
    if (fetchEmails) {
        fetchEmails()
    }
    setTimeout(() => {
        isRefreshing.value = false
    }, 1000)
}

const loadSettings = (): void => {
    const savedSettings = localStorage.getItem('mailweb-settings')
    if (savedSettings) {
        try {
            const parsedSettings = JSON.parse(savedSettings)
            settings.value = { ...settings.value, ...parsedSettings }
            emit('update:settings', settings.value)
        } catch (e) {
            console.error('Failed to parse settings from localStorage:', e)
        }
    }
}

const saveSettings = (): void => {
    localStorage.setItem('mailweb-settings', JSON.stringify(settings.value))
    emit('update:settings', settings.value)
    showSettingsDialog.value = false
}

onMounted(() => {
    loadSettings()
    if (isPollingActive) {
        startPolling()
    }
    if (paginationTriggered) {
        watch(paginationTriggered, (triggered) => {
            if (triggered && isPolling.value) {
                stopPolling()
                toast.success('Auto-refresh has been disabled while you\'re browsing. You can re-enable it in the bottom left corner')
            }
        })
    }
})

onUnmounted(() => {
    stopPolling()
})

const sendTestEmail = async (): Promise<void> => {
    isSending.value = true
    try {
        const response = await axios.get('/mailweb/send-test-email', {
            headers: {
                'Accept': 'application/json'
            }
        })

        if (response.status === 200) {
            toast.success('Test email sent successfully')
        } else {
            toast.error('Failed to send test email')
        }
    } catch (error) {
        toast.error('Failed to send test email')
        console.error('Error sending test email:', error)
    } finally {
        isSending.value = false
    }
}

const deleteAllEmails = async (): Promise<void> => {
    if (!isDeleteAllEnabled.value) {
        showDeleteAllDialog.value = false
        showDeleteAllDisabledDialog.value = true
        return
    }

    isDeleting.value = true
    try {
        const response = await axios.delete('/mailweb/emails/delete-all', {
            headers: {
                'Accept': 'application/json'
            }
        })

        if (response.status === 200) {
            toast.success('All emails have been deleted successfully!')
            if (fetchEmails) {
                fetchEmails()
            }
        } else {
            toast.error('Failed to delete all emails')
        }
    } catch (error: any) {
        if (error.response && error.response.status === 403) {
            toast.error('Delete all emails feature is disabled')
            showDeleteAllDisabledDialog.value = true
        } else {
            toast.error('Error deleting all emails')
            console.error('Error deleting all emails:', error)
        }
    } finally {
        isDeleting.value = false
        showDeleteAllDialog.value = false
    }
}
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

    <!-- Settings Dialog -->
    <Dialog :open="showSettingsDialog" @update:open="showSettingsDialog = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Settings</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Pagination Amount</label>
                    <Select v-model="settings.paginationAmount">
                        <SelectTrigger>
                            <SelectValue :placeholder="`${settings.paginationAmount} items per page`" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in paginationOptions" :key="option" :value="option">
                                {{ option }} items per page
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Date Format</label>
                    <Select v-model="settings.dateFormat">
                        <SelectTrigger>
                            <SelectValue placeholder="Select date format" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in dateFormatOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="my-4" v-if="isDeleteAllEnabled">
                    <Button variant="destructive"
                        @click="isDeleteAllEnabled ? showDeleteAllDialog = true : showDeleteAllDisabledDialog = true"
                        class="w-full">
                        <Trash2 class="h-4 w-4" /> Delete All Emails
                    </Button>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showSettingsDialog = false">Cancel</Button>
                <Button @click="saveSettings">Save Changes</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete All Confirmation Dialog -->
    <Dialog :open="showDeleteAllDialog" @update:open="showDeleteAllDialog = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-destructive">Delete All Emails</DialogTitle>
            </DialogHeader>
            <div class="py-4">
                <p class="text-sm text-muted-foreground">
                    Are you sure you want to delete all emails? This action cannot be undone.
                </p>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteAllDialog = false">Cancel</Button>
                <Button variant="destructive" @click="deleteAllEmails()" :disabled="isDeleting">
                    <span v-if="isDeleting">Deleting...</span>
                    <span v-else>Delete All</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete All Disabled Dialog -->
    <Dialog :open="showDeleteAllDisabledDialog" @update:open="showDeleteAllDisabledDialog = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <AlertCircle class="h-5 w-5 text-amber-500" />
                    Feature Disabled
                </DialogTitle>
            </DialogHeader>
            <div class="py-4">
                <p class="text-sm text-muted-foreground">
                    The "Delete All Emails" feature is currently disabled by your administrator.
                </p>
                <p class="text-sm text-muted-foreground mt-2">
                    To enable this feature, set <code>MAILWEB_DELETE_ALL_ENABLED=true</code> in your environment
                    variables or update the config file.
                </p>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteAllDisabledDialog = false">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>