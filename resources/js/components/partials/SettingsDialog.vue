<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { AlertCircle, Trash2 } from 'lucide-vue-next';
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
    { value: 'days-ago', label: 'X Days ago' },
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
            toast.success('All emails have been deleted successfully!');
            emit('update:settings', settings.value); // Trigger refresh in parent
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
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Settings</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Pagination Amount</label>
                    <Select v-model="settings.paginationAmount">
                        <SelectTrigger>
                            <SelectValue :placeholder="`${settings.paginationAmount} items per page`" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in paginationOptions" :key="option" :value="option"> {{ option }} items per page </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Date Format</label>
                    <Select v-model="settings.dateFormat">
                        <SelectTrigger>
                            <SelectValue placeholder="Select date format" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in dateFormatOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="my-4" v-if="isDeleteAllEnabled">
                    <Button variant="destructive" @click="showDeleteAllDialog = true" class="w-full">
                        <Trash2 class="h-4 w-4" /> Delete All Emails
                    </Button>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="$emit('update:showSettingsDialog', false)">Cancel</Button>
                <Button @click="saveSettings">Save Changes</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete All Confirmation Dialog -->
    <Dialog :open="showDeleteAllDialog" @update:open="showDeleteAllDialog = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-destructive">Delete All Emails</DialogTitle>
            </DialogHeader>
            <div class="py-4">
                <p class="text-sm text-muted-foreground">Are you sure you want to delete all emails? This action cannot be undone.</p>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteAllDialog = false">Cancel</Button>
                <Button variant="destructive" @click="deleteAllEmails()" :disabled="isDeleting">
                    <span v-if="isDeleting">Deleting...</span>
                    <span v-else>Delete All</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete All Disabled Dialog -->
    <Dialog :open="showDeleteAllDisabledDialog" @update:open="showDeleteAllDisabledDialog = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <AlertCircle class="h-5 w-5 text-amber-500" />
                    Feature Disabled
                </DialogTitle>
            </DialogHeader>
            <div class="py-4">
                <p class="text-sm text-muted-foreground">The "Delete All Emails" feature is currently disabled by your administrator.</p>
                <p class="mt-2 text-sm text-muted-foreground">
                    To enable this feature, set <code>MAILWEB_DELETE_ALL_ENABLED=true</code> in your environment variables or update the config file.
                </p>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteAllDisabledDialog = false">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
