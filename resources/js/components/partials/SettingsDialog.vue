<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { AlertCircle, Settings, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import toast from 'vue3-hot-toast';

const props = defineProps<{
    showSettingsDialog: boolean;
    isDeleteAllEnabled: boolean;
    initialSettings: Record<string, any>;
}>();

const emit = defineEmits(['update:showSettingsDialog', 'update:settings']);

const settings = ref({ ...props.initialSettings });
const showDeleteAllDialog = ref<boolean>(false);
const showDeleteAllDisabledDialog = ref<boolean>(false);
const isDeleting = ref<boolean>(false);

const paginationOptions: number[] = [10, 25, 50, 100];
const dateFormatOptions = [
    { value: 'timestamp', label: 'Timestamp' },
    { value: 'uk', label: 'UK (DD/MM/YYYY)' },
    { value: 'us', label: 'US (MM/DD/YYYY)' },
    { value: 'days-ago', label: 'Relative (X days ago)' },
];

watch(
    () => props.initialSettings,
    (newSettings) => {
        settings.value = { ...newSettings };
    },
);

const saveSettings = (): void => {
    localStorage.setItem('mailweb-settings', JSON.stringify(settings.value));
    emit('update:settings', settings.value);
    emit('update:showSettingsDialog', false);
};

const deleteAllEmails = async (): Promise<void> => {
    if (!props.isDeleteAllEnabled) {
        showDeleteAllDialog.value = false;
        showDeleteAllDisabledDialog.value = true;
        return;
    }

    isDeleting.value = true;
    try {
        const response = await axios.delete('/mailweb/emails/delete-all', {
            headers: { Accept: 'application/json' },
        });

        if (response.status === 200) {
            toast.success('All emails deleted');
            emit('update:settings', settings.value);
        } else {
            toast.error('Failed to delete all emails');
        }
    } catch (error: any) {
        if (error.response && error.response.status === 403) {
            toast.error('Delete all emails feature is disabled');
            showDeleteAllDisabledDialog.value = true;
        } else {
            toast.error('Error deleting all emails');
            console.error('Error deleting all emails:', error);
        }
    } finally {
        isDeleting.value = false;
        showDeleteAllDialog.value = false;
    }
};
</script>

<template>
    <!-- Settings Dialog -->
    <Dialog :open="showSettingsDialog" @update:open="$emit('update:showSettingsDialog', $event)">
        <DialogContent class="sm:max-w-[420px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <div class="bg-muted flex h-8 w-8 items-center justify-center rounded-lg">
                        <Settings class="text-muted-foreground h-4 w-4" />
                    </div>
                    Settings
                </DialogTitle>
            </DialogHeader>
            <div class="grid gap-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Items per page</label>
                    <Select v-model="settings.paginationAmount">
                        <SelectTrigger>
                            <SelectValue :placeholder="`${settings.paginationAmount} items`" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in paginationOptions" :key="option" :value="option">{{ option }} items</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Date format</label>
                    <Select v-model="settings.dateFormat">
                        <SelectTrigger>
                            <SelectValue placeholder="Select format" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in dateFormatOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div v-if="isDeleteAllEnabled" class="border-t pt-3">
                    <Button variant="destructive" @click="showDeleteAllDialog = true" class="w-full text-sm">
                        <Trash2 class="mr-1.5 h-3.5 w-3.5" /> Delete All Emails
                    </Button>
                </div>
            </div>
            <DialogFooter class="gap-2 pt-2 sm:gap-0">
                <Button variant="outline" @click="$emit('update:showSettingsDialog', false)" class="text-sm">Cancel</Button>
                <Button @click="saveSettings" class="text-sm">Save</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete All Confirmation Dialog -->
    <Dialog :open="showDeleteAllDialog" @update:open="showDeleteAllDialog = $event">
        <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
                <DialogTitle class="text-destructive flex items-center gap-2">
                    <div class="bg-destructive/10 flex h-8 w-8 items-center justify-center rounded-lg">
                        <Trash2 class="text-destructive h-4 w-4" />
                    </div>
                    Delete All Emails
                </DialogTitle>
            </DialogHeader>
            <div class="py-3">
                <p class="text-muted-foreground text-sm leading-relaxed">Are you sure? This will permanently delete all captured emails.</p>
            </div>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="showDeleteAllDialog = false" class="text-sm">Cancel</Button>
                <Button variant="destructive" @click="deleteAllEmails()" :disabled="isDeleting" class="text-sm">
                    <span v-if="isDeleting">Deleting...</span>
                    <span v-else>Delete All</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete All Disabled Dialog -->
    <Dialog :open="showDeleteAllDisabledDialog" @update:open="showDeleteAllDisabledDialog = $event">
        <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10">
                        <AlertCircle class="h-4 w-4 text-amber-500" />
                    </div>
                    Feature Disabled
                </DialogTitle>
            </DialogHeader>
            <div class="py-3">
                <p class="text-muted-foreground text-sm leading-relaxed">The "Delete All" feature is disabled by your administrator.</p>
                <p class="text-muted-foreground mt-2 text-sm leading-relaxed">
                    Set <code class="bg-muted rounded px-1.5 py-0.5 text-xs font-mono">MAILWEB_DELETE_ALL_ENABLED=true</code> in your environment to enable.
                </p>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteAllDisabledDialog = false" class="text-sm">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
