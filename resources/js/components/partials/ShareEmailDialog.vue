<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Switch } from '@/components/ui/switch';
import type { EmailPreview } from '@/types/email';
import axios from 'axios';
import type { Bitmap2D } from 'lean-qr';
import { generate } from 'lean-qr';
import { toSvg, toSvgDataURL } from 'lean-qr/extras/svg';
import { Check, Copy, Eye, QrCode, Share2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    email: EmailPreview;
}>();

const emit = defineEmits(['update:open']);

// Reactive state
const shareUrlCopied = ref(false);
const isToggling = ref(false);
const shareUrl = ref('');
const qrCode = ref<Bitmap2D | null>(null);
const urlInputRef = ref<HTMLInputElement | null>(null);
const qrSvgRef = ref<SVGElement | null>(null);
const qrSvgUrl = ref<string>('');
const qrCodeError = ref<string>('');

// Generate QR code and render as SVG
const generateQrCode = (url: string) => {
    if (!url) return;

    // Reset error state
    qrCodeError.value = '';

    try {
        // Generate QR code
        const qrCodeBitmap = generate(url);
        qrCode.value = qrCodeBitmap;

        // Generate SVG data URL
        qrSvgUrl.value = toSvgDataURL(qrCodeBitmap, {
            on: '#000000',
            off: 'transparent',
            padX: 4,
            padY: 4,
            scale: 4,
        });

        // Render SVG element on next tick to ensure DOM is updated
        setTimeout(() => {
            if (qrSvgRef.value && qrCodeBitmap) {
                // Create new SVG with QR code
                toSvg(qrCodeBitmap, qrSvgRef.value, {
                    on: '#000000',
                    off: 'transparent',
                    padX: 4,
                    padY: 4,
                    width: 200,
                    height: 200,
                });
            }
        }, 0);
    } catch (error) {
        console.error('Error generating QR code:', error);
        qrCode.value = null;
        qrSvgUrl.value = '';
        qrCodeError.value = 'Failed to generate QR code';
    }
};

// Watch for dialog opening
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.email.share_enabled && props.email.share_url) {
            shareUrl.value = props.email.share_url;
            generateQrCode(props.email.share_url);
        }
    },
);

// Clipboard functions
const copyShareUrl = async (): Promise<void> => {
    if (!shareUrl.value) return;

    try {
        if (navigator.clipboard) {
            await navigator.clipboard.writeText(shareUrl.value);
        } else {
            fallbackCopyTextToClipboard(shareUrl.value);
        }
        shareUrlCopied.value = true;
        setTimeout(() => (shareUrlCopied.value = false), 2000);
    } catch (error) {
        console.error('Failed to copy to clipboard:', error);
        fallbackCopyTextToClipboard(shareUrl.value);
    }
};

const fallbackCopyTextToClipboard = (text: string): void => {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        shareUrlCopied.value = true;
        setTimeout(() => (shareUrlCopied.value = false), 2000);
    } catch (error) {
        console.error('Fallback copy failed:', error);
    } finally {
        document.body.removeChild(textArea);
    }
};

const toggleShareEnabled = async (): Promise<void> => {
    if (!props.email || isToggling.value) return;

    isToggling.value = true;
    try {
        const response = await axios.post<{ share_enabled: number; share_url?: string }>(`/mailweb/emails/${props.email.id}/toggle-share`);
        if (response.data.share_enabled) {
            shareUrl.value = response.data.share_url || '';
            generateQrCode(shareUrl.value);
        } else {
            shareUrl.value = '';
            qrCode.value = null;
        }
        props.email.share_enabled = response.data.share_enabled;
    } catch (error) {
        console.error('Error toggling share status:', error);
    } finally {
        isToggling.value = false;
    }
};

const closeDialog = () => {
    emit('update:open', false);
};

const selectAllText = () => {
    if (urlInputRef.value) {
        urlInputRef.value.select();
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="flex max-h-[90vh] max-w-[1000px] flex-col justify-between p-4 sm:max-w-[700px] sm:p-6">
            <div class="flex min-h-0 flex-1 flex-col space-y-4">
                <DialogHeader>
                    <DialogTitle>Share Email</DialogTitle>
                </DialogHeader>

                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <Share2 class="h-5 w-5 text-primary" />
                        <span class="text-sm font-medium">Email Sharing</span>
                    </div>
                    <Switch :model-value="email.share_enabled" @update:model-value="toggleShareEnabled" :disabled="isToggling" />
                </div>

                <div v-if="email.share_enabled" class="flex flex-col space-y-4">
                    <div class="flex flex-col items-center gap-4 sm:flex-row">
                        <div class="flex-shrink-0 rounded-lg border bg-white p-2 shadow-sm">
                            <svg v-if="qrCode" ref="qrSvgRef" class="h-48 w-48"></svg>
                            <img v-else-if="qrSvgUrl" :src="qrSvgUrl" alt="QR Code" class="h-48 w-48" />
                            <div
                                v-else-if="qrCodeError"
                                class="flex h-48 w-48 flex-col items-center justify-center rounded bg-gray-100 p-4 dark:bg-gray-800"
                            >
                                <QrCode class="mb-2 h-8 w-8 text-red-400" />
                                <p class="text-center text-xs font-medium text-red-500">QR Code Error</p>
                                <p class="mt-1 text-center text-xs text-gray-500">{{ qrCodeError }}</p>
                            </div>
                            <div v-else class="flex h-48 w-48 items-center justify-center rounded bg-gray-100 dark:bg-gray-800">
                                <div class="animate-pulse">
                                    <QrCode class="h-8 w-8 text-gray-300" />
                                </div>
                            </div>
                        </div>
                        <div class="max-w-full flex-1 space-y-1 text-sm">
                            <h3 class="font-medium">QR Code</h3>
                            <p class="text-gray-500 dark:text-gray-400">Scan this code with a mobile device to view the email.</p>
                            <p class="hidden text-xs text-gray-400 dark:text-gray-500 sm:block">Anyone with this code can access the content.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm font-medium">Share Link</h3>
                        <div class="flex items-center gap-2">
                            <div class="flex min-w-0 flex-1 items-center overflow-hidden rounded-md border bg-gray-50 dark:bg-gray-800">
                                <input
                                    ref="urlInputRef"
                                    type="text"
                                    readonly
                                    :value="shareUrl"
                                    @dblclick="selectAllText"
                                    @focus="selectAllText"
                                    class="w-full flex-1 border-none bg-transparent px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="copyShareUrl"
                                    class="h-full flex-shrink-0 border-l px-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <Check v-if="shareUrlCopied" class="h-3 w-3 text-green-500" />
                                    <Copy v-else class="h-3 w-3" />
                                    <span class="ml-1 text-xs">{{ shareUrlCopied ? 'Copied!' : 'Copy' }}</span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center px-2 py-4 text-center">
                    <div class="mb-2 rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                        <QrCode class="h-6 w-6 text-gray-400" />
                    </div>
                    <h3 class="mb-1 text-sm font-medium">Enable sharing to continue</h3>
                    <p class="max-w-xs text-xs text-gray-500 dark:text-gray-400">Toggle the switch above to generate a shareable link and QR code.</p>
                </div>
            </div>

            <DialogFooter class="mt-auto">
                <Button variant="outline" @click="closeDialog" class="text-xs">Close</Button>
                <Button as="a" v-if="email.share_enabled && shareUrl" variant="default" :href="shareUrl" target="_blank" class="ml-2 text-xs">
                    <Eye class="mr-1 h-3 w-3" />
                    Preview
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
