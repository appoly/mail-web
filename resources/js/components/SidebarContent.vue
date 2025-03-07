<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, inject } from 'vue'
import { Search, Mail, Filter, RefreshCw, Settings, X, Send, Play, Pause, ArrowLeft } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog'
import axios from 'axios'

import Github from '@/components/icons/Github.vue'

const props = defineProps<{
    searchQuery: string
    filters: Record<string, any>
    isMobile: boolean
}>()

const emit = defineEmits(['update:searchQuery', 'update:filters', 'close-sidebar', 'update:settings'])

// Inject the fetchEmails function from Dashboard
const fetchEmails = inject('fetchEmails') as () => void

const isRefreshing = ref<boolean>(false)
const isSending = ref<boolean>(false)
const isPolling = ref<boolean>(false)
const pollingInterval = ref<number | null>(null)

// Settings
const showSettingsDialog = ref<boolean>(false)
const paginationOptions = [10, 25, 50, 100]
const dateFormatOptions = [
    { value: 'timestamp', label: 'Timestamp' },
    { value: 'uk', label: 'UK (DD/MM/YYYY)' },
    { value: 'us', label: 'US (MM/DD/YYYY)' },
    { value: 'days-ago', label: 'X Days ago' }
]

// Default settings
const settings = ref({
    paginationAmount: 25,
    dateFormat: 'timestamp'
})

// Poll for new emails every 10 seconds
const POLLING_INTERVAL_MS = 10000

const startPolling = (): void => {
    if (pollingInterval.value !== null) return

    // Immediately fetch emails
    handleRefresh()

    // Start polling
    pollingInterval.value = window.setInterval(() => {
        if (document.visibilityState === 'visible') {
            handleRefresh()
        }
    }, POLLING_INTERVAL_MS)

    isPolling.value = true
}

const stopPolling = (): void => {
    if (pollingInterval.value !== null) {
        window.clearInterval(pollingInterval.value)
        pollingInterval.value = null
    }
    isPolling.value = false
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

// Handle visibility change to pause polling when tab is not visible
const handleVisibilityChange = (): void => {
    if (document.visibilityState === 'hidden' && isPolling.value) {
        // Don't stop the interval, just don't fetch when hidden
    }
}

// Load settings from local storage
const loadSettings = (): void => {
    const savedSettings = localStorage.getItem('mailweb-settings')
    if (savedSettings) {
        try {
            const parsedSettings = JSON.parse(savedSettings)
            settings.value = {
                ...settings.value,
                ...parsedSettings
            }
            // Emit the loaded settings to parent components
            emit('update:settings', settings.value)
        } catch (e) {
            console.error('Failed to parse settings from localStorage:', e)
        }
    }
}

// Save settings to local storage
const saveSettings = (): void => {
    localStorage.setItem('mailweb-settings', JSON.stringify(settings.value))
    emit('update:settings', settings.value)
    showSettingsDialog.value = false
}

// Clean up interval when component is unmounted
onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibilityChange)
    loadSettings()
})

onUnmounted(() => {
    stopPolling()
    document.removeEventListener('visibilitychange', handleVisibilityChange)
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
            alert('Test email sent successfully! Check your logs.')
        } else {
            alert('Failed to send test email. Please check the console for details.')
            console.error('Failed to send test email:', response.data)
        }
    } catch (error) {
        alert('Error sending test email')
        console.error('Error sending test email:', error)
    } finally {
        isSending.value = false
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
                <Input placeholder="Search emails..." class="pl-8" :value="searchQuery"
                    @input="$emit('update:searchQuery', $event.target.value)" />
            </div>
        </div>

        <div v-if="!isMobile" class="p-4 border-b">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-medium">Filters</h2>
                <Button variant="ghost" size="sm" class="h-8 px-2">
                    <Filter class="h-4 w-4" />
                </Button>
            </div>

            <div class="space-y-3">
                <div class="space-y-1">
                    <label class="text-xs font-medium">Status</label>
                    <Select :value="filters.status"
                        @update:value="$emit('update:filters', { ...filters, status: $event })">
                        <SelectTrigger class="h-8">
                            <SelectValue placeholder="All" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="sent">Sent</SelectItem>
                            <SelectItem value="queued">Queued</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium">Time Range</label>
                    <Select :value="filters.timeRange"
                        @update:value="$emit('update:filters', { ...filters, timeRange: $event })">
                        <SelectTrigger class="h-8">
                            <SelectValue placeholder="All time" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All time</SelectItem>
                            <SelectItem value="today">Today</SelectItem>
                            <SelectItem value="yesterday">Yesterday</SelectItem>
                            <SelectItem value="week">This week</SelectItem>
                            <SelectItem value="month">This month</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </div>

        <div class="mt-auto">
            <div class="p-4 border-t">
                <a href="/" class="text-primary text-xs hover:underline">
                    <ArrowLeft class="inline-block w-4 h-4" /> Return to app
                </a>
            </div> 

            <div class="p-4 border-t">
                <TooltipProvider>
                    <div class="flex items-center justify-between">
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button :variant="isPolling ? 'default' : 'ghost'" size="icon" @click="togglePolling"
                                    class="relative">
                                    <Play v-if="!isPolling" class="h-4 w-4" />
                                    <Pause v-else class="h-4 w-4" />
                                    <RefreshCw v-if="isRefreshing" class="h-4 w-4 animate-spin absolute" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>{{ isPolling ? 'Stop' : 'Start' }} Auto-Refresh</TooltipContent>
                        </Tooltip>

                        <template v-if="!isMobile">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="ghost" size="icon" @click="showSettingsDialog = true">
                                        <Settings class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>Settings</TooltipContent>
                            </Tooltip>



                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="ghost" size="icon" @click="sendTestEmail" :disabled="isSending">
                                        <Send :class="['h-4 w-4', { 'animate-pulse': isSending }]" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>Send Test Email</TooltipContent>
                            </Tooltip>

                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button as="a" href="https://github.com/appoly/mail-web" target="_blank"
                                        rel="noopener noreferrer" variant="ghost" size="icon">
                                        <Github class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>GitHub Repository</TooltipContent>
                            </Tooltip>
                        </template>
                    </div>
                </TooltipProvider>
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
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showSettingsDialog = false">Cancel</Button>
                <Button @click="saveSettings">Save Changes</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
