<script setup lang="ts">
import { EmailAttachment } from '@/types/email';
import { ref } from 'vue';
import AttachmentDialog from './partials/AttachmentDialog.vue';
import AttachmentIcon from './ui/AttachmentIcon.vue';

defineProps<{
    attachments: EmailAttachment[];
}>();

// Dialog state
const isDialogOpen = ref(false);
const selectedAttachment = ref<EmailAttachment | null>(null);

const handleAttachmentClick = (attachment: EmailAttachment) => {
    selectedAttachment.value = attachment;
    isDialogOpen.value = true;
};

const getFileType = (filename: string) => {
    return filename.split('.').pop() || 'unknown';
};
</script>

<template>
    <div>
        <div class="mb-2 flex items-center gap-2">
            <span class="text-sm font-medium">Attachments ({{ attachments.length }})</span>
        </div>
        <div class="relative">
            <div class="no-scrollbar flex gap-3 overflow-x-auto pb-2">
                <div
                    v-for="attachment in attachments"
                    :key="attachment.id"
                    @click="handleAttachmentClick(attachment)"
                    class="w-60 flex-shrink-0 cursor-pointer rounded-md border p-3 transition-colors hover:bg-gray-50"
                >
                    <div class="flex items-center gap-3">
                        <AttachmentIcon :filename="attachment.name" />
                        <div class="overflow-hidden">
                            <div class="truncate text-sm font-medium" :title="attachment.name">{{ attachment.name }}</div>
                            <div class="flex items-center gap-1 text-xs text-gray-500">
                                <span class="uppercase">{{ getFileType(attachment.name) }}</span> • {{ attachment.human_readable_size }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachment Dialog -->
        <AttachmentDialog v-model:open="isDialogOpen" :attachment="selectedAttachment" />
    </div>
</template>
