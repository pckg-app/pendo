import VueRouter from 'vue-router'

const router = new VueRouter({
    mode: 'history',
    routes: [
/*        {
            path: '/configure/:apiKey', component: SomeComponent
        }*/
    ]
});

router.beforeEach(function (to, from, next) {
    /**
     * @T00D00 - make a request to the server and see about route?
     * Server can return route and definition?
     */

    /**
     * When redirecting from non-vue to vue.
     */
    if (from.matched.length === 0 && to.matched.length > 0 && from.fullPath !== '/') {
        next(false);
        http.redirect(to.fullPath);
        return;
    }

    /**
     * Auth guard.
     */
    let redirected = false;
    if (to.meta.tags) {
        $.each(to.meta.tags, function (i, routeTag) {
            if (routeTag.indexOf('group:') !== 0 && routeTag.indexOf('auth:') !== 0 && routeTag.indexOf('permission:') !== 0) {
                return;
            }
            if ($store.getters.userHasTag(routeTag)) {
                return;
            }
            console.log('no tag', routeTag);
            next(Pckg.auth.user.id ? '/unauthorized' : '/login');
            redirected = true;
            return false;
        })
    }

    if (redirected) {
        return;
    }

    next();
});

router.afterEach(function (to, from) {
    if (typeof ga === 'undefined') {
        return;
    }

    if (from && to.path === from.path) {
        return;
    }

    ga('set', 'page', to.path);
    ga('send', 'pageview');
});

export default router