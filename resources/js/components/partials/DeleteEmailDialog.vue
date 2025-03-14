<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

defineProps<{
    open: boolean
}>()

const emit = defineEmits(['update:open', 'confirm'])

const closeDialog = () => {
    emit('update:open', false)
}

const confirmDelete = () => {
    emit('confirm')
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Delete Email</DialogTitle>
            </DialogHeader>
            <div class="py-4">
                <p>Are you sure you want to delete this email? This action cannot be undone.</p>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="closeDialog">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
