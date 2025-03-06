<script setup lang="ts">
import { ref } from 'vue'
import { ChevronDown, ChevronUp, Paperclip } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge'
import {
    Accordion,
    AccordionItem,
    AccordionTrigger,
    AccordionContent,
} from '@/components/ui/accordion'
import { EmailDetails as Email } from '@/types/email'

defineProps<{
    email: Email
    isMobile: boolean
}>()

const isExpanded = ref(false)
const activeAccordion = ref<string | undefined>(undefined)

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <div class="flex-1 overflow-auto">
        <!-- Header -->
        <div class="p-2 sm:p-4 border-b flex items-center justify-between">
            <h3 class="font-medium">Email Details</h3>
            <Button v-if="!isMobile" variant="ghost" size="sm" class="h-7 px-2 text-xs sm:text-sm"
                @click="isExpanded = !isExpanded">
                <template v-if="isExpanded">
                    <ChevronDown class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
                    <span class="hidden sm:inline">Collapse</span>
                </template>
                <template v-else>
                    <ChevronUp class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
                    <span class="hidden sm:inline">Expand</span>
                </template>
            </Button>
        </div>

        <!-- Details -->
        <div :class="['p-2 sm:p-4', isMobile || isExpanded ? '' : 'max-h-40 overflow-hidden']">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-medium mb-1">From</h4>
                    <p class="text-sm break-words">{{ email.from }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-1">To</h4>
                    <p class="text-sm break-words">{{ email.to }}</p>
                </div>

                <div v-if="email.cc">
                    <h4 class="text-sm font-medium mb-1">CC</h4>
                    <p class="text-sm break-words">{{ email.cc }}</p>
                </div>

                <div v-if="email.bcc">
                    <h4 class="text-sm font-medium mb-1">BCC</h4>
                    <p class="text-sm break-words">{{ email.bcc }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-1">Subject</h4>
                    <p class="text-sm break-words">{{ email.subject }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-1">Date</h4>
                    <p class="text-sm">{{ formatDate(email.date) }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-1">Status</h4>
                    <Badge :variant="email.status === 'sent' ? 'default' : email.status === 'failed' ? 'destructive' : 'outline'
                        ">
                        {{ email.status.charAt(0).toUpperCase() + email.status.slice(1) }}
                    </Badge>
                </div>
            </div>

            <Separator v-if="!isMobile" class="my-4" />

            <!-- Accordion -->
            <Accordion v-model="activeAccordion" type="single" collapsible class="w-full">
                <AccordionItem v-if="!isMobile" value="headers">
                    <AccordionTrigger>Headers</AccordionTrigger>
                    <AccordionContent>
                        <div class="bg-muted p-2 rounded-md">
                            <pre class="text-xs whitespace-pre-wrap overflow-x-auto">{{ email.headers }}</pre>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <AccordionItem v-if="email.attachments && email.attachments.length > 0" value="attachments">
                    <AccordionTrigger>Attachments ({{ email.attachments.length }})</AccordionTrigger>
                    <AccordionContent>
                        <div class="space-y-2">
                            <div v-for="(attachment, index) in email.attachments" :key="index"
                                class="flex items-center p-2 bg-muted rounded-md">
                                <Paperclip class="h-4 w-4 mr-2 flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ attachment.filename }}</p>
                                    <p class="text-xs text-muted-foreground">{{ attachment.size }}</p>
                                </div>
                                <Button variant="outline" size="sm" class="ml-2 flex-shrink-0">Download</Button>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <AccordionItem value="events">
                    <AccordionTrigger>Events</AccordionTrigger>
                    <AccordionContent>
                        <div class="space-y-2">
                            <div v-for="(event, index) in email.events" :key="index"
                                class="flex flex-col sm:flex-row sm:items-center justify-between p-2 bg-muted rounded-md gap-1">
                                <div>
                                    <p class="text-sm font-medium">{{ event.type }}</p>
                                    <p class="text-xs text-muted-foreground">{{ event.description }}</p>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1 sm:mt-0">
                                    {{ formatDate(event.timestamp) }}
                                </p>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>

        <div v-if="!isMobile && !isExpanded"
            class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-background to-transparent pointer-events-none" />
    </div>
</template>

