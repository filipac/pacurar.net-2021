<template>
    <p>test - {{counter.count}}</p>

    <button @click="counter.increment">+ to have it {{ counter.nextCount}}</button>
</template>

<script>
import { useCounterStore } from './stores/counter'
export default {
    name: "App",
    setup() {
        const counter = useCounterStore()
        return {
            counter
        }
    }
}
</script>

<style scoped>

</style>
