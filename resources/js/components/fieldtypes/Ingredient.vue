<template>

    <div>
        <text-input :value="q" v-model="q" @input="onInput"/>
        <input type="hidden" :value="value"/>
        <ul v-show="items.length > 0">
            <li v-for="item in items" @click="onSelect(item)">
                {{ item.label }}
            </li>
        </ul>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    mounted() {
        if (this.value !== '') {
            this.fetchFromId()
        }
    },

    methods: {
        async fetchFromId() {
            const response = await fetch('/ingredients/show/' + this.value)
            let result = await response.json()
            if (result.length !== 0) {
                this.q = result['label']
            }
        },
        async onInput() {
            if (this.q.length > 2) {
                const response = await fetch('/ingredients/list/' + this.q)
                this.items = await response.json()
            } else {
                this.items = []
            }
        },
        onSelect(item) {
            this.update(item.code)
            this.q = item.label
            this.items = []
        }
    },

    data() {
        return {
            items: [],
            q: ''
        }
    }

}
</script>
