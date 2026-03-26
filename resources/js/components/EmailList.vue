<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Email, EmailAddress } from '@/types/email';
import { AlertCircle, Inbox, Loader2, Paperclip, Search } from 'lucide-vue-next';
import { inject, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    emails: Array<Email>;
    selectedEmail: Email | null;
    isLoading: boolean;
    isLoadingMore?: boolean;
    error: string | null;
    isMobile: boolean;
    totalEmails?: number;
    hasMoreEmails?: boolean;
    isSearching?: boolean;
    searchQuery?: string;
    isFiltered?: boolean;
}>();

// Track if this is the first load
const isFirstLoad = ref(true);

const emit = defineEmits(['update:selectedEmail', 'loadMore']);

const scrollRef = ref<HTMLElement | null>(null);
const isIntersecting = ref(false);

// Setup intersection observer for infinite scrolling
let observer: IntersectionObserver | null = null;

// Get the display name or first part of email
const getDisplayName = (address: EmailAddress): string => {
    if (address.name) return address.name;
    return address.address.split('@')[0];
};

// Get full email addresses for tooltip
const getFullEmailAddresses = (addresses: EmailAddress[]): string => {
    return addresses.map((addr) => (addr.name ? `${addr.name} <${addr.address}>` : addr.address)).join(', ');
};

const formatDate = inject('formatDate') as (dateString: string) => string;
const paginationTriggered = inject('paginationTriggered', null) as any;
const isPollingActive = inject('isPollingActive', null) as any;

// Helper function to truncate text
const truncateText = (text: string, maxLength: number = 80): string => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength - 3) + '...';
};

// Setup intersection observer for infinite scrolling
const setupIntersectionObserver = () => {
    if (!window.IntersectionObserver) return;

    observer = new IntersectionObserver(
        (entries) => {
            const target = entries[0];
            isIntersecting.value = target.isIntersecting;
            if (isIntersecting.value && props.hasMoreEmails && !props.isLoadingMore && !props.isLoading) {
                if (isPollingActive && paginationTriggered) {
                    if (isPollingActive.value === true) {
                        paginationTriggered.value = true;
                    }
                }
                emit('loadMore');
            }
        },
        { threshold: 0.1 },
    );

    const scrollTarget = document.getElementById('scroll-target');
    if (scrollTarget) {
        observer.observe(scrollTarget);
    }
};

// Lifecycle hooks
onMounted(() => {
    setupIntersectionObserver();
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
        setTimeout(() => {
            if (observer) {
                observer.disconnect();
            }
            setupIntersectionObserver();

            if (props.emails.length > 0) {
                isFirstLoad.value = false;
            }
        }, 100);
    },
);

watch(isPollingActive, () => {
    if (observer) {
        observer.disconnect();
    }
    setupIntersectionObserver();
});
</script>

<template>
    <div class="flex h-full flex-col border-r lg:w-[380px]">
        <div v-if="error" class="text-destructive p-6 text-center">
            <div class="bg-destructive/10 mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl">
                <AlertCircle class="h-6 w-6" />
            </div>
            <p class="font-medium">Failed to load emails</p>
            <p class="text-muted-foreground mt-1 text-sm">{{ error }}</p>
        </div>

        <template v-else>
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-[0.95rem] font-semibold tracking-tight">Inbox</h2>
                <Badge variant="secondary" class="tabular-nums text-xs font-medium">
                    <template v-if="isFiltered">{{ emails.length }} of {{ props.totalEmails || 0 }}</template>
                    <template v-else>{{ props.totalEmails || emails.length }}</template>
                </Badge>
            </div>
            <Separator />

            <!-- Searching indicator (non-first-load) -->
            <div v-if="isLoading && !isFirstLoad && isSearching" class="flex items-center gap-2 border-b px-5 py-2.5">
                <Loader2 class="text-primary h-3.5 w-3.5 animate-spin" />
                <span class="text-muted-foreground text-xs">Searching{{ searchQuery ? ` for "${searchQuery}"` : '' }}...</span>
            </div>

            <!-- Loading Skeletons (first load only) -->
            <div v-if="isLoading && isFirstLoad" class="flex flex-col">
                <div v-for="(_, i) in 8" :key="i" class="flex flex-col gap-2 border-b px-5 py-3.5">
                    <div class="flex items-center justify-between">
                        <Skeleton class="h-3.5 w-32" />
                        <Skeleton class="h-3 w-14" />
                    </div>
                    <Skeleton class="h-3.5 w-3/4" />
                    <Skeleton class="h-3 w-full" />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!isLoading && emails.length === 0" class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                <div class="bg-muted mb-4 flex h-14 w-14 items-center justify-center rounded-xl">
                    <template v-if="searchQuery">
                        <Search class="text-muted-foreground h-7 w-7" />
                    </template>
                    <template v-else>
                        <Inbox class="text-muted-foreground h-7 w-7" />
                    </template>
                </div>
                <p class="font-medium">{{ searchQuery ? 'No results' : 'No emails found' }}</p>
                <p class="text-muted-foreground mt-1 text-sm">
                    {{ searchQuery ? `Nothing matched "${searchQuery}"` : 'Emails will appear here when captured' }}
                </p>
            </div>

            <!-- Email List -->
            <ScrollArea v-else class="flex-1" ref="scrollRef">
                <div class="flex flex-col">
                    <div
                        v-for="(email, index) in emails"
                        :key="email.id"
                        :class="[
                            'group relative flex cursor-pointer flex-col px-5 py-3 transition-[background-color,border-color] duration-150',
                            selectedEmail?.id === email.id
                                ? 'bg-accent border-l-2 border-l-primary'
                                : 'border-l-2 border-l-transparent hover:bg-muted/50',
                        ]"
                        :style="{ animationDelay: index < 10 ? `${index * 30}ms` : '0ms' }"
                        class="animate-fade-in-up"
                        @click="emit('update:selectedEmail', email)"
                    >
                        <!-- Row 1: Recipient + metadata -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-1.5">
                                <span class="truncate text-sm font-medium">
                                    <template v-if="email.to && email.to.length > 0">
                                        {{ getDisplayName(email.to[0]) }}
                                    </template>
                                    <span v-else class="text-destructive italic">No recipients</span>
                                </span>
                                <TooltipProvider v-if="email.to && email.to.length > 1">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Badge variant="secondary" class="h-4 px-1.5 text-[0.65rem]">+{{ email.to.length - 1 }}</Badge>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <div class="max-w-[250px] text-xs break-words">
                                                {{ getFullEmailAddresses(email.to) }}
                                            </div>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <div
                                    v-if="email.attachments_count && email.attachments_count > 0"
                                    class="text-muted-foreground flex items-center gap-0.5 text-xs"
                                >
                                    <Paperclip class="h-3 w-3" />
                                    <span>{{ email.attachments_count }}</span>
                                </div>
                                <span class="text-muted-foreground/60 text-xs">{{ formatDate(email.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Row 2: Subject -->
                        <div class="mt-1 truncate text-[0.8rem] font-medium leading-snug">{{ email.subject }}</div>

                        <!-- Row 3: Preview (2 lines) -->
                        <div class="text-muted-foreground mt-0.5 line-clamp-2 text-xs leading-relaxed">{{ email.body_text }}</div>
                    </div>

                    <!-- Infinite scroll loading indicator -->
                    <div v-if="props.isLoadingMore" class="flex justify-center py-4">
                        <Loader2 class="text-primary h-5 w-5 animate-spin" />
                    </div>

                    <!-- Intersection observer target -->
                    <div v-if="props.hasMoreEmails" class="h-1" id="scroll-target"></div>

                    <!-- End of list indicator -->
                    <div v-if="emails.length > 0 && !props.hasMoreEmails" class="text-muted-foreground/50 py-4 text-center text-xs">
                        No more emails
                    </div>
                </div>
            </ScrollArea>
        </template>
    </div>
</template>
