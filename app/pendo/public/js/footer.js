import Vue from "vue/dist/vue.esm.browser.js";
/**
 * Register main Vue event dispatcher.
 * Dispatcher is shared with parent window so we transmit all events between iframes and host.
 *
 * @type {Vue}
 */
window.$dispatcher = (new Vue());

//import store from "./store.js";
import router from "./router.js";
import components from "./components.js";

window.pckgCdn = {
    methods: {
        cdn: function(a){
            return a;
        }
    }
};

//window.$router = router;

//const data = data || {};
//const props = props || {};
//window.$store = store;

window.$vue = new Vue({
    el: '#vue-app',
    //  $store,
    router,
    components,
    data: function () {
        return {
            localBus: new Vue(),
        };
    },
    computed: {
        '$store': function () {
            return $store;
        },
    }
});
