<script setup lang="ts">
import { CheckCircle2, AlertCircle, Clock } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Email } from '@/types/email'

defineProps<{
    emails: Array<Email>,
    selectedEmail: Email | null,
    isLoading: boolean,
    error: Error | null,
    isMobile: boolean
}>()

defineEmits(['update:selectedEmail'])

const getStatusIcon = (status: string): { icon: any; class: string } | null => {
    switch (status) {
        case 'sent':
            return { icon: CheckCircle2, class: 'h-4 w-4 text-green-500' }
        case 'failed':
            return { icon: AlertCircle, class: 'h-4 w-4 text-destructive' }
        case 'queued':
            return { icon: Clock, class: 'h-4 w-4 text-amber-500' }
        default:
            return null
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <div :class="['w-full flex flex-col h-full', { 'lg:w-80': !isMobile }]">
        <div v-if="error" class="w-full lg:w-80 p-4 flex flex-col h-full">
            <div class="text-destructive p-4 text-center">
                <AlertCircle class="h-8 w-8 mx-auto mb-2" />
                <p>Failed to load emails</p>
                <p class="text-sm text-muted-foreground">{{ error.message }}</p>
            </div>
        </div>

        <template v-else>
            <div class="flex-1 overflow-auto">
                <div v-if="isLoading" class="p-4 space-y-4">
                    <div v-for="(_, i) in 10" :key="i" class="space-y-2">
                        <Skeleton class="h-4 w-3/4" />
                        <Skeleton class="h-4 w-1/2" />
                        <Skeleton class="h-4 w-1/4" />
                    </div>
                </div>

                <div v-else-if="emails.length === 0" class="p-8 text-center text-muted-foreground">
                    <p>No emails found</p>
                </div>

                <div v-else>
                    <Button v-for="email in emails" :key="email.id" variant="ghost" :class="[
                        'w-full justify-start p-3 h-auto text-left rounded-none',
                        selectedEmail?.id === email.id ? 'bg-muted' : '',
                        isMobile ? 'touch-action-manipulation' : ''
                    ]" @click="$emit('update:selectedEmail', email)">
                        <div class="flex flex-col w-full gap-1">
                            <div class="flex items-center justify-between w-full">
                                <span class="font-medium truncate">{{ email.subject }}</span>
                                <component :is="getStatusIcon(email.status)?.icon" :class="getStatusIcon(email.status)?.class" />
                            </div>
                            <div class="flex items-center justify-between w-full text-xs text-muted-foreground">
                                <span class="truncate">{{ email.from }}</span>
                                <span>{{ formatDate(email.date) }}</span>
                            </div>
                            <p class="text-xs text-muted-foreground truncate">{{ email.preview }}</p>
                        </div>
                    </Button>
                </div>
            </div>

            <div v-if="!isMobile" class="p-4 border-t">
                <p class="text-xs text-muted-foreground">Showing {{ emails.length }} emails</p>
            </div>
        </template>
    </div>
</template>
