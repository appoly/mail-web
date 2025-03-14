<script setup lang="ts">
import { computed } from 'vue';
import {
    FileText,
    FileSpreadsheet,
    FileImage,
    FileCode,
    FileArchive,
    FileAudio,
    FileVideo,
    FileTerminal,
    File // Generic fallback
} from 'lucide-vue-next';

// Define props with TypeScript
const props = defineProps<{
    filename: string;
}>();

// Function to determine the Lucide icon and color class
const getFileIconAndColor = (filename: string): { icon: any; color: string } => {
    const ext = filename.split('.').pop()?.toLowerCase() || '';

    switch (true) {
        case ['xls', 'xlsx', 'csv'].includes(ext):
            return { icon: FileSpreadsheet, color: 'text-green-600' };
        case ['doc', 'docx', 'rtf'].includes(ext):
            return { icon: FileText, color: 'text-blue-600' };
        case ['pdf'].includes(ext):
            return { icon: FileText, color: 'text-red-600' };
        case ['jpg', 'jpeg', 'gif'].includes(ext):
            return { icon: FileImage, color: 'text-yellow-500' };
        case ['png', 'bmp'].includes(ext):
            return { icon: FileImage, color: 'text-orange-500' };
        case ['txt', 'md'].includes(ext):
            return { icon: FileText, color: 'text-gray-600' };
        case ['js', 'ts', 'py', 'php', 'html', 'css'].includes(ext):
            return { icon: FileCode, color: 'text-purple-600' };
        case ['zip', 'rar', 'tar', 'gz'].includes(ext):
            return { icon: FileArchive, color: 'text-brown-600' };
        case ['ppt', 'pptx', 'key'].includes(ext):
            return { icon: FileText, color: 'text-teal-600' };
        case ['mp3', 'wav', 'ogg'].includes(ext):
            return { icon: FileAudio, color: 'text-pink-500' };
        case ['mp4', 'avi', 'mkv'].includes(ext):
            return { icon: FileVideo, color: 'text-fuchsia-600' };
        case ['exe', 'bat', 'sh'].includes(ext):
            return { icon: FileTerminal, color: 'text-gray-900' };
        default:
            return { icon: File, color: 'text-gray-400' };
    }
};

// Computed properties for icon and color
const fileIcon = computed(() => getFileIconAndColor(props.filename).icon);
const fileColorClass = computed(() => getFileIconAndColor(props.filename).color);
</script>

<template>
    <!-- Add a fixed-size wrapper div to ensure consistent dimensions -->
    <div class="flex items-center justify-center w-6 h-6">
        <component :is="fileIcon" :class="[fileColorClass, 'w-5 h-5']" />
    </div>
</template>