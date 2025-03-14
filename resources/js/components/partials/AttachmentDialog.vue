<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmailAttachment } from '@/types/email';
import { Download, FileX } from 'lucide-vue-next';
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

    // Create a temporary anchor element
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
                    <AttachmentIcon v-if="attachment" :filename="attachment.name" />
                    <span>{{ attachment?.name || 'Attachment' }}</span>
                </DialogTitle>
            </DialogHeader>

            <div class="py-4">
                <!-- No attachment case -->
                <div v-if="!attachment" class="flex flex-col items-center justify-center rounded-md bg-gray-50 p-8 text-center">
                    <FileX class="mb-4 h-16 w-16 text-gray-400" />
                    <p class="mb-2 text-gray-600">Attachment not found</p>
                </div>

                <!-- Missing file URL case -->
                <div v-else-if="!attachment.file_url" class="flex flex-col items-center justify-center rounded-md bg-gray-50 p-8 text-center">
                    <FileX class="mb-4 h-16 w-16 text-gray-400" />
                    <p class="mb-2 text-gray-600">Attachment was not stored</p>
                </div>

                <!-- Image attachment -->
                <div v-else-if="isImage" class="flex justify-center">
                    <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-primary"></div>
                    </div>
                    <img
                        v-if="attachment.file_url"
                        :src="attachment.file_url"
                        :alt="attachment.name"
                        class="max-h-[400px] rounded border object-contain"
                        @load="handleImageLoaded"
                        @error="handleImageError"
                    />
                    <div v-if="imageError" class="mt-4 text-center text-sm text-gray-500">
                        <p>Unable to display image</p>
                    </div>
                </div>

                <!-- Non-image attachment -->
                <div v-else-if="attachment" class="rounded-md bg-gray-50 p-8 text-center">
                    <div class="mb-4 flex justify-center">
                        <AttachmentIcon :filename="attachment.name" class="h-16 w-16" />
                    </div>
                    <p class="mb-2 text-gray-600">Preview not available for this file type</p>
                    <p class="mb-4 text-sm text-gray-500">{{ attachment.human_readable_size }}</p>
                    <Button v-if="attachment.file_url" @click="downloadAttachment">
                        <Download class="mr-2 h-4 w-4" />
                        Download
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
