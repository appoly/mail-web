<script setup lang="ts">
// Is it pointless, yes. Is it cool, also yes!
import { Button } from '@/components/ui/button';
import { Send } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    disabled: boolean;
}>();

const emit = defineEmits(['click']);

const isAnimating = ref(false);

const handleClick = () => {
    if (!props.disabled) {
        isAnimating.value = true;
        emit('click');
        setTimeout(() => {
            isAnimating.value = false;
        }, 1000); // Total duration: 0.5s fly off + 0.5s fly back
    }
};
</script>

<template>
    <Button variant="ghost" size="icon" :disabled="disabled" @click="handleClick">
        <Send :class="['h-4 w-4', { 'animate-fly-sequence': isAnimating, 'animate-pulse': disabled && !isAnimating }]" />
    </Button>
</template>

<style scoped>
.animate-fly-sequence {
    animation:
        flyOff 0.5s ease-out,
        flyBack 0.5s ease-in 0.5s;
}

@keyframes flyOff {
    0% {
        transform: translate(0, 0);
        opacity: 1;
    }

    100% {
        transform: translate(10px, -10px);
        /* Fly to top-right */
        opacity: 0;
    }
}

@keyframes flyBack {
    0% {
        transform: translate(-10px, 10px);
        /* Start from bottom-left */
        opacity: 0;
    }

    100% {
        transform: translate(0, 0);
        /* Return to original position */
        opacity: 1;
    }
}
</style>
