<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { EmailAttachment } from '@/types/email';
import { Download, FileX, Loader2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AttachmentIcon from '../ui/AttachmentIcon.vue';

const props = defineProps<{
    open: boolean;
    attachment: EmailAttachment | null;
}>();

const emit = defineEmits(['update:open']);

// Reactive state
const isImage = ref(false);
const isLoading = ref(true);
const imageError = ref(false);

// Check if the attachment is an image based on file extension
const checkIfImage = (filename: string): boolean => {
    if (!filename) return false;
    const extension = filename.split('.').pop()?.toLowerCase() || '';
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(extension);
};

// Reset state when attachment changes
const resetState = () => {
    isLoading.value = true;
    imageError.value = false;
};

// Watch for dialog opening and attachment changes
watch(
    [() => props.open, () => props.attachment],
    ([isOpen, attachment]) => {
        if (isOpen && attachment) {
            resetState();
            isImage.value = checkIfImage(attachment.name);
        }
    },
    { immediate: true },
);

// Handle image load complete
const handleImageLoaded = () => {
    isLoading.value = false;
};

// Handle image load error
const handleImageError = () => {
    isLoading.value = false;
    imageError.value = true;
};

// Download the attachment
const downloadAttachment = () => {
    if (!props.attachment?.file_url) return;

    const link = document.createElement('a');
    link.href = props.attachment.file_url;
    link.download = props.attachment.name;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <div class="bg-muted flex h-8 w-8 items-center justify-center rounded-lg">
                        <AttachmentIcon v-if="attachment" :filename="attachment.name" />
                    </div>
                    <span class="truncate">{{ attachment?.name || 'Attachment' }}</span>
                </DialogTitle>
            </DialogHeader>

            <div class="py-3">
                <!-- No attachment case -->
                <div v-if="!attachment" class="flex flex-col items-center justify-center rounded-xl bg-muted/50 p-10 text-center">
                    <FileX class="text-muted-foreground/50 mb-3 h-12 w-12" />
                    <p class="text-muted-foreground text-sm font-medium">Attachment not found</p>
                </div>

                <!-- Missing file URL case -->
                <div v-else-if="!attachment.file_url" class="flex flex-col items-center justify-center rounded-xl bg-muted/50 p-10 text-center">
                    <FileX class="text-muted-foreground/50 mb-3 h-12 w-12" />
                    <p class="text-muted-foreground text-sm font-medium">Attachment was not stored</p>
                </div>

                <!-- Image attachment -->
                <div v-else-if="isImage" class="flex justify-center">
                    <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                        <Loader2 class="text-primary h-6 w-6 animate-spin" />
                    </div>
                    <img
                        v-if="attachment.file_url"
                        :src="attachment.file_url"
                        :alt="attachment.name"
                        class="max-h-[400px] rounded-lg border object-contain"
                        @load="handleImageLoaded"
                        @error="handleImageError"
                    />
                    <div v-if="imageError" class="text-muted-foreground mt-4 text-center text-sm">
                        <p>Unable to display image</p>
                    </div>
                </div>

                <!-- Non-image attachment -->
                <div v-else-if="attachment" class="rounded-xl bg-muted/50 p-10 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="bg-background flex h-16 w-16 items-center justify-center rounded-xl shadow-sm">
                            <AttachmentIcon :filename="attachment.name" class="h-8 w-8" />
                        </div>
                    </div>
                    <p class="text-muted-foreground text-sm font-medium">Preview not available</p>
                    <p class="text-muted-foreground/70 mt-1 text-xs">{{ attachment.human_readable_size }}</p>
                    <Button v-if="attachment.file_url" @click="downloadAttachment" class="mt-4 text-sm">
                        <Download class="mr-1.5 h-3.5 w-3.5" />
                        Download
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
