<script setup lang="ts">
    import { EmailAttachment } from '@/types/email';
    import AttachmentIcon from './ui/AttachmentIcon.vue';

    const props = defineProps<{
        attachments: EmailAttachment[]
    }>()

    const handleAttachmentClick = (attachment: EmailAttachment) => {
        console.log(attachment)
    }

    const getFileType = (filename: string) => {
        return filename.split('.').pop() || 'unknown'
    }
</script>

<template>
    <div>
        <div class="flex items-center gap-2 mb-2">
            <span class="text-sm font-medium">Attachments ({{ attachments.length }})</span>
        </div>
        <div class="relative">
            <div class="flex overflow-x-auto pb-2 gap-3 no-scrollbar">
                <div v-for="attachment in attachments" :key="attachment.id" 
                    @click="handleAttachmentClick(attachment)"
                    class="flex-shrink-0 border rounded-md p-3 cursor-pointer hover:bg-gray-50 transition-colors
                    w-60"
                    >
                    <div class="flex items-center gap-3">
                        <AttachmentIcon :filename="attachment.name" />
                        <div class="overflow-hidden">
                            <div class="text-sm font-medium truncate" :title="attachment.name">{{ attachment.name }}</div>
                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                <span class="uppercase">{{ getFileType(attachment.name) }}</span>
                            </div>
                            <!-- <div class="text-xs text-gray-500 flex items-center gap-1">
                                <span class="capitalize">txt</span> • 100kb
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>