var app = new Vue({
    el: '#app',
    data() {
        return {
            playing: null,
            ws: null,
            bar: [],
            danceFloor: [],
        };
    },
    created: function() {
        this.connect();
    },
    methods: {
        connect () {
            const ws = new WebSocket(config.webSocketUrl);

            ws.onclose = (e) => {
                console.log('Socket is closed. Reconnect will be attempted in 1 second.', e.reason);
                setTimeout(this.connect, 1000);
            };

            ws.onopen = (e) => {
                this.fetchData();
            };

            ws.onmessage = (e) => {
                if ('genreChanged' === e?.data) {
                    this.fetchData();
                }
            };

            ws.onerror = (err) => {
                console.error('Socket encountered error: ', err.message, 'Closing socket');
                this.ws.close();
            };
        },
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
            fetch(`${config.apiUrl}/genre`, {method: "POST"});
        }
    },
    mounted () {
    },
});
