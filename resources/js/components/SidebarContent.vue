<script setup lang="ts">
import { ref } from 'vue'
import { Search, Mail, Filter, RefreshCw, Settings, HelpCircle, X } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

// defineProps({
//     searchQuery: String,
//     filters: Object,
//     isMobile: Boolean
// })

defineProps<{
    searchQuery: string
    filters: Record<string, any>
    isMobile: boolean
}>()

defineEmits(['update:searchQuery', 'update:filters', 'close-sidebar'])

const isRefreshing = ref<boolean>(false)

const handleRefresh = () : void => {
    isRefreshing.value = true
    setTimeout(() => {
        isRefreshing.value = false
    }, 1000)
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

        <div class="p-4 border-t mt-auto">
            <TooltipProvider>
                <div class="flex items-center justify-between">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" @click="handleRefresh">
                                <RefreshCw :class="['h-4 w-4', { 'animate-spin': isRefreshing }]" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Refresh</TooltipContent>
                    </Tooltip>

                    <template v-if="!isMobile">
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <Settings class="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Settings</TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <HelpCircle class="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Help</TooltipContent>
                        </Tooltip>
                    </template>
                </div>
            </TooltipProvider>
        </div>
    </div>
</template>

