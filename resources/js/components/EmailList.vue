<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, inject } from 'vue'
import { AlertCircle, Loader2 } from 'lucide-vue-next'
import { Skeleton } from '@/components/ui/skeleton'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Separator } from '@/components/ui/separator'
import { Email, EmailAddress } from '@/types/email'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

const props = defineProps<{
    emails: Array<Email>,
    selectedEmail: Email | null,
    isLoading: boolean,
    isLoadingMore?: boolean,
    error: Error | null,
    isMobile: boolean,
    totalEmails?: number,
    hasMoreEmails?: boolean
}>()

// Track if this is the first load
const isFirstLoad = ref(true)

const emit = defineEmits(['update:selectedEmail', 'loadMore'])

const scrollRef = ref<HTMLElement | null>(null)
const isIntersecting = ref(false)

// Setup intersection observer for infinite scrolling
let observer: IntersectionObserver | null = null

// Helper function to format email addresses with limit
const formatEmailAddress = (address: EmailAddress): string => {
    return address.name ? `${address.name} <${address.address}>` : address.address
}

// Get full email addresses for tooltip
const getFullEmailAddresses = (addresses: EmailAddress[]): string => {
    return addresses.map(addr => formatEmailAddress(addr)).join(', ')
}

const formatDate = inject('formatDate') as (dateString: string) => string
const paginationTriggered = inject('paginationTriggered', null) as any
const isPollingActive = inject('isPollingActive', null) as any



// Helper function to truncate text
const truncateText = (text: string, maxLength: number = 60): string => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, 57) + '...';
}

// Setup intersection observer for infinite scrolling
const setupIntersectionObserver = () => {
    if (!window.IntersectionObserver) return;
    
    observer = new IntersectionObserver(
        (entries) => {
            const target = entries[0];
            isIntersecting.value = target.isIntersecting;
            if (isIntersecting.value && props.hasMoreEmails && !props.isLoadingMore && !props.isLoading) {
                // If polling is active, set the pagination trigger before loading more
                if (isPollingActive && paginationTriggered) {
                    if (isPollingActive.value === true) {
                        paginationTriggered.value = true;
                    }
                }
                emit('loadMore');
            }
        },
        { threshold: 0.1 }
    );
    
    // Observe the scroll target element
    const scrollTarget = document.getElementById('scroll-target');
    if (scrollTarget) {
        observer.observe(scrollTarget);
    }
};

// Lifecycle hooks
onMounted(() => {
    setupIntersectionObserver();
    // Reset first load when component is mounted
    isFirstLoad.value = true;
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});

// Watch for changes in emails to re-setup observer
watch(
    () => props.emails.length,
    () => {
        // Wait for DOM update
        setTimeout(() => {
            if (observer) {
                observer.disconnect();
            }
            setupIntersectionObserver();
            
            // Set isFirstLoad to false after emails are loaded
            if (props.emails.length > 0) {
                isFirstLoad.value = false;
            }
        }, 100);
    }
);

watch(isPollingActive, () => {
    if (observer) {
        observer.disconnect();
    }
    setupIntersectionObserver();
})
</script>

<template>
    <div class="flex h-full flex-col lg:w-[350px]">
        <div v-if="error" class="text-destructive p-4 text-center">
            <AlertCircle class="h-8 w-8 mx-auto mb-2" />
            <p>Failed to load emails</p>
            <p class="text-sm text-muted-foreground">{{ error.message }}</p>
        </div>

        <template v-else>
            <div class="flex items-center justify-between p-4">
                <h2 class="text-lg font-semibold">Emails</h2>
                <div class="text-sm text-muted-foreground">
                    {{ props.totalEmails ? `${emails.length} of ${props.totalEmails}` : emails.length }} emails
                </div>
            </div>
            <Separator />
            
            <div v-if="isLoading && isFirstLoad" class="flex flex-col">
                <div v-for="(_, i) in 10" :key="i" class="flex cursor-pointer flex-col gap-1 border-b p-4">
                    <!-- Email header with recipient and timestamp -->
                    <div class="flex items-center justify-between">
                        <Skeleton class="h-4 w-40" />
                        <Skeleton class="h-3 w-16" />
                    </div>
                    <!-- Email subject -->
                    <div class="mt-1">
                        <Skeleton class="h-4 w-3/4" />
                    </div>
                    <!-- Email preview text -->
                    <div class="mt-1">
                        <Skeleton class="h-3 w-full" />
                    </div>
                </div>
            </div>

            <div v-else-if="!isLoading && emails.length === 0" class="p-8 text-center text-muted-foreground">
                <p>No emails found</p>
            </div>

            <ScrollArea v-else class="flex-1" ref="scrollRef">
                <div class="flex flex-col">
                    <div
                        v-for="email in emails"
                        :key="email.id"
                        :class="[
                            'flex cursor-pointer flex-col gap-1 border-b p-4 hover:bg-muted/50',
                            selectedEmail?.id === email.id ? 'bg-muted' : ''
                        ]"
                        @click="emit('update:selectedEmail', email)"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-medium flex items-center">
                                {{ formatEmailAddress(email.to[0]) }}
                                <TooltipProvider v-if="email.to.length > 1">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <span class="text-xs text-muted-foreground ml-1 cursor-pointer hover:text-primary">
                                                + {{ email.to.length - 1 }} more
                                            </span>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <div class="text-xs max-w-[250px] break-words">
                                                {{ getFullEmailAddresses(email.to) }}
                                            </div>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ formatDate(email.created_at) }}
                            </div>
                        </div>
                        <div class="text-sm font-medium">{{ email.subject }}</div>
                        <div class="text-xs text-muted-foreground">{{ truncateText(email.body_text) }}</div>
                    </div>
                    

                    
                    <!-- Infinite scroll loading indicator -->
                    <div v-if="props.isLoadingMore" class="p-4 flex justify-center">
                        <Loader2 class="h-6 w-6 animate-spin text-primary" />
                    </div>
                    
                    <!-- Intersection observer target -->
                    <div v-if="props.hasMoreEmails" class="h-1" id="scroll-target"></div>
                    
                    <!-- End of list indicator -->
                    <div v-if="emails.length > 0 && !props.hasMoreEmails" class="p-4 text-center text-sm text-muted-foreground">
                        End of emails
                    </div>
                </div>
            </ScrollArea>
        </template>
    </div>
</template>
