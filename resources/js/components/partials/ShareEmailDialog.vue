<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Switch } from '@/components/ui/switch';
import type { EmailPreview } from '@/types/email';
import axios from 'axios';
import type { Bitmap2D } from 'lean-qr';
import { generate } from 'lean-qr';
import { toSvg, toSvgDataURL } from 'lean-qr/extras/svg';
import { Check, Copy, ExternalLink, Link2, QrCode, Share2 } from 'lucide-vue-next';
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

    qrCodeError.value = '';

    try {
        const qrCodeBitmap = generate(url);
        qrCode.value = qrCodeBitmap;

        qrSvgUrl.value = toSvgDataURL(qrCodeBitmap, {
            on: '#000000',
            off: 'transparent',
            padX: 4,
            padY: 4,
            scale: 4,
        });

        setTimeout(() => {
            if (qrSvgRef.value && qrCodeBitmap) {
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
        <DialogContent class="flex max-h-[90vh] max-w-[1000px] flex-col justify-between p-5 sm:max-w-[640px] sm:p-6">
            <div class="flex min-h-0 flex-1 flex-col space-y-5">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <div class="bg-primary/10 flex h-8 w-8 items-center justify-center rounded-lg">
                            <Share2 class="text-primary h-4 w-4" />
                        </div>
                        Share Email
                    </DialogTitle>
                </DialogHeader>

                <!-- Toggle -->
                <div class="flex items-center justify-between rounded-xl border bg-muted/30 p-3.5">
                    <div class="flex items-center gap-2.5">
                        <Link2 class="text-muted-foreground h-4 w-4" />
                        <div>
                            <span class="text-sm font-medium">Public sharing</span>
                            <p class="text-muted-foreground text-xs">Anyone with the link can view</p>
                        </div>
                    </div>
                    <Switch :model-value="email.share_enabled" @update:model-value="toggleShareEnabled" :disabled="isToggling" />
                </div>

                <!-- Enabled state -->
                <div v-if="email.share_enabled" class="flex flex-col space-y-5">
                    <div class="flex flex-col items-center gap-5 sm:flex-row">
                        <div class="shrink-0 overflow-hidden rounded-xl border bg-white p-2 shadow-sm">
                            <svg v-if="qrCode" ref="qrSvgRef" class="h-44 w-44"></svg>
                            <img v-else-if="qrSvgUrl" :src="qrSvgUrl" alt="QR Code" class="h-44 w-44" />
                            <div
                                v-else-if="qrCodeError"
                                class="flex h-44 w-44 flex-col items-center justify-center rounded-lg bg-destructive/5 p-4"
                            >
                                <QrCode class="text-destructive/50 mb-2 h-8 w-8" />
                                <p class="text-destructive text-center text-xs font-medium">QR Code Error</p>
                                <p class="text-muted-foreground mt-1 text-center text-xs">{{ qrCodeError }}</p>
                            </div>
                            <div v-else class="flex h-44 w-44 items-center justify-center rounded-lg bg-muted">
                                <QrCode class="text-muted-foreground/30 h-8 w-8 animate-pulse" />
                            </div>
                        </div>
                        <div class="max-w-full flex-1 space-y-1.5 text-sm">
                            <h3 class="font-medium">QR Code</h3>
                            <p class="text-muted-foreground text-sm leading-relaxed">Scan with a mobile device to view this email.</p>
                            <p class="text-muted-foreground/70 hidden text-xs sm:block">Anyone with this code can access the content.</p>
                        </div>
                    </div>

                    <!-- Share Link -->
                    <div class="space-y-2">
                        <h3 class="text-sm font-medium">Share link</h3>
                        <div class="flex items-center gap-2">
                            <div class="flex min-w-0 flex-1 items-center overflow-hidden rounded-lg border bg-muted/30">
                                <input
                                    ref="urlInputRef"
                                    type="text"
                                    readonly
                                    :value="shareUrl"
                                    @dblclick="selectAllText"
                                    @focus="selectAllText"
                                    class="focus:ring-primary w-full flex-1 border-none bg-transparent px-3 py-2 text-xs focus:ring-1 focus:outline-hidden"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="copyShareUrl"
                                    class="h-full shrink-0 border-l px-3 hover:bg-muted"
                                >
                                    <Check v-if="shareUrlCopied" class="h-3.5 w-3.5 text-green-500" />
                                    <Copy v-else class="h-3.5 w-3.5" />
                                    <span class="ml-1.5 text-xs">{{ shareUrlCopied ? 'Copied!' : 'Copy' }}</span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disabled state -->
                <div v-else class="flex flex-col items-center justify-center px-4 py-8 text-center">
                    <div class="bg-muted mb-3 flex h-14 w-14 items-center justify-center rounded-xl">
                        <QrCode class="text-muted-foreground h-7 w-7" />
                    </div>
                    <h3 class="text-sm font-medium">Enable sharing</h3>
                    <p class="text-muted-foreground mt-1 max-w-xs text-xs leading-relaxed">Toggle the switch above to generate a shareable link and QR code.</p>
                </div>
            </div>

            <DialogFooter class="mt-auto gap-2 sm:gap-0">
                <Button variant="outline" @click="closeDialog" class="text-sm">Close</Button>
                <Button as="a" v-if="email.share_enabled && shareUrl" variant="default" :href="shareUrl" target="_blank" class="text-sm">
                    <ExternalLink class="mr-1.5 h-3.5 w-3.5" />
                    Preview
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
