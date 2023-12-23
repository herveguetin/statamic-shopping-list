<template>

    <div>
        <text-input :value="value" @input="onInput"/>
        <ul v-show="items.length > 0">
            <li v-for="item in items">
                {{ item.name }}
            </li>
        </ul>

    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    methods: {
        async onInput(q) {
            if (q.length > 2) {
                const response = await fetch('/ingredients/list?' + new URLSearchParams({q: q}))
                this.items = await response.json()
            } else {
                this.items = []
            }
        }
    },

    data() {
        return {
            items: []
        }
    }

}
</script>
