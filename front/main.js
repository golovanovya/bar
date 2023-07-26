var app = new Vue({
    el: '#app',
    data() {
        return {
            playing: null,
            bar: [],
            danceFloor: [],
        };
    },
    methods: {
        fetchData() {
            fetch(`${config.apiUrl}`)
                .then(data => data.json())
                .then(data => {
                    this.playing = data.playing;
                    this.bar = data.bar;
                    this.danceFloor = data.dance_floor;
                });
        },
        change() {
            fetch(`${config.apiUrl}/genre`, {method: "POST"})
                .then(data => data.json())
                .then(data => this.fetchData());
        }
    },
    mounted () {
        this.fetchData();
    },
});
