<template>
    <TransitionGroup
        name="dada-coin"
        tag="div"
        class="dada-coin-container"
        @after-leave="onAnimationComplete"
    >
        <div
            v-for="coin in activeCoins"
            :key="coin.id"
            class="dada-coin"
        >
            <div class="coin-icon">🪙</div>
            <div class="coin-amount">+{{ coin.amount }}</div>
        </div>
    </TransitionGroup>
</template>

<script setup>
import { ref, nextTick } from 'vue'

const activeCoins = ref([])
let coinIdCounter = 0

const props = defineProps({
    duration: { type: Number, default: 1500 }
})

const emit = defineEmits(['complete'])

const showReward = (amount) => {
    const id = ++coinIdCounter
    activeCoins.value.push({ id, amount })
    
    // Auto-remove after animation
    setTimeout(() => {
        const idx = activeCoins.value.findIndex(c => c.id === id)
        if (idx !== -1) {
            activeCoins.value.splice(idx, 1)
        }
    }, props.duration)
}

const onAnimationComplete = () => {
    emit('complete')
}

defineExpose({ showReward })
</script>

<style scoped>
.dada-coin-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 50;
    pointer-events: none;
}

.dada-coin {
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: coinFloat 1.5s ease-out forwards;
}

.coin-icon {
    font-size: 36px;
    animation: coinSpin 0.8s ease-out;
    filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.6));
}

.coin-amount {
    font-size: 20px;
    font-weight: 800;
    color: #FFD700;
    text-shadow:
        0 0 10px rgba(255, 215, 0, 0.8),
        0 2px 4px rgba(0, 0, 0, 0.5);
    margin-top: -4px;
    background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@keyframes coinFloat {
    0% {
        opacity: 1;
        transform: translateY(0) scale(0.5);
    }
    30% {
        opacity: 1;
        transform: translateY(-30px) scale(1.2);
    }
    70% {
        opacity: 1;
        transform: translateY(-80px) scale(1);
    }
    100% {
        opacity: 0;
        transform: translateY(-120px) scale(0.8);
    }
}

@keyframes coinSpin {
    0% { transform: rotateY(0deg) scale(0.5); }
    50% { transform: rotateY(360deg) scale(1.3); }
    100% { transform: rotateY(720deg) scale(1); }
}

.dada-coin-enter-active {
    animation: coinFloat 1.5s ease-out forwards;
}

.dada-coin-leave-active {
    animation: coinFloat 0.3s ease-in reverse;
}
</style>
