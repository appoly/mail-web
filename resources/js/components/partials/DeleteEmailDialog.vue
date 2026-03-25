<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertCircle } from 'lucide-vue-next';

defineProps<{
    open: boolean;
}>();

const emit = defineEmits(['update:open', 'confirm']);

const closeDialog = () => {
    emit('update:open', false);
};

const confirmDelete = () => {
    emit('confirm');
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <div class="bg-destructive/10 flex h-8 w-8 items-center justify-center rounded-lg">
                        <AlertCircle class="text-destructive h-4 w-4" />
                    </div>
                    Delete Email
                </DialogTitle>
            </DialogHeader>
            <div class="py-3">
                <p class="text-muted-foreground text-sm leading-relaxed">Are you sure you want to delete this email? This action cannot be undone.</p>
            </div>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="closeDialog" class="text-sm">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete" class="text-sm">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
