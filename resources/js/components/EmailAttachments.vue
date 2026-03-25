<script setup lang="ts">
import { EmailAttachment } from '@/types/email';
import { Paperclip } from 'lucide-vue-next';
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
        <div class="mb-2 flex items-center gap-1.5">
            <Paperclip class="text-muted-foreground h-3.5 w-3.5" />
            <span class="text-xs font-medium">{{ attachments.length }} attachment{{ attachments.length !== 1 ? 's' : '' }}</span>
        </div>
        <div class="relative">
            <div class="no-scrollbar flex gap-2 overflow-x-auto pb-1">
                <div
                    v-for="attachment in attachments"
                    :key="attachment.id"
                    @click="handleAttachmentClick(attachment)"
                    class="group w-56 shrink-0 cursor-pointer rounded-md border p-2.5 transition-all duration-150 hover:border-primary/30 hover:bg-accent/50"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="bg-muted flex h-8 w-8 shrink-0 items-center justify-center rounded">
                            <AttachmentIcon :filename="attachment.name" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-xs font-medium leading-snug" :title="attachment.name">{{ attachment.name }}</div>
                            <div class="text-muted-foreground flex items-center gap-1 text-[0.65rem]">
                                <span class="uppercase">{{ getFileType(attachment.name) }}</span>
                                <span class="opacity-40">&middot;</span>
                                <span>{{ attachment.human_readable_size }}</span>
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
