
require('./bootstrap');
import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import {routes} from './routes';
import StoreData from './store';
import MainApp from './components/MainApp.vue';
import {initialize} from './helpers/general';
import { getRoleByString } from "./helpers/roles";
import { createLog } from "./helpers/log";
import VueNoty from 'vuejs-noty'
import VuejsDialog from 'vuejs-dialog';
import 'vuejs-dialog/dist/vuejs-dialog.min.css';

Vue.use(VueNoty)
Vue.use(VuejsDialog);
Vue.use(VueRouter);
Vue.use(Vuex);

const store = new Vuex.Store(StoreData);
var appName = "Examen-afname";

const router = new VueRouter({
    routes,
    mode: 'history'
});

router.beforeEach((to, from, next) => {
    // console.log("--------------------------------");
    // console.log(to.name);
    //cant find route in routes.js
    if(to.matched.length <= 0) {
        // console.log("didn't find next route in routes.js");
        //didn't find next route in routes.js
        if(from.matched.length <= 0) {
            // console.log("didn't find previous route in routes.js");
            //didn't find previous route in routes.js
            next('');
        } else {
            // console.log("found match in routes.js");
            //found match in routes.js
            document.title = from.meta.title + " | " + appName;
            next(from.path);
        }
    } else {
        // console.log("found match in routes.js");
        //found match in routes.js
        var rolesOld = to.meta.roles;
        var roles = [];
        for (var i = 0; i < rolesOld.length; i++) {
            roles[i] = getRoleByString(rolesOld[i]);
        }

        var hasUser = store.state.currentUser != null ? true : false;
        var hasSession = store.state.session != null ? true : false;

        //needs authorization
        if(roles.length <= 0){
            // console.log("doesn't need authorization");
            if((hasUser && hasSession) && (to.path == '/login' || to.path == '/register')){
                next('/examinerhome');
            }
            //doesn't need authorization
            var requiresInvitation = to.matched.some(record => record.meta.requireInvitationToken);
            if(requiresInvitation){
                // console.log("needs invitation");
                //needs invitation
                var urlVar = to.query.invitation_token;
                // var urlVar = this.$route.query.invitation_token;
                if(urlVar != null && urlVar != undefined && urlVar.length == 32){
                    // console.log("has invitation token");
                    //has invitation token
                    document.title = to.meta.title + " | " + appName;
                    next();
                }else{
                    // console.log("doesn't have an invitation token");
                    //doesn't have an invitation token
                    document.title = from.meta.title + " | " + appName;
                    next('/login');
                }
            } else {
                // console.log("doesn't need invitation");
                //doesn't need invitation
                document.title = to.meta.title + " | " + appName;
                next();
            }
        } else {
            // console.log("needs authorization");
            if(hasUser && hasSession){
                // console.log("if the user and the session is true");
                //if the user and the session is true
                var userRole = store.state.currentUser.role_id;
                // console.log("Role is: " + userRole);
                if(roles.includes(userRole)){
                    // console.log("if your role has access to the page");
                    //if your role has access to the page
                    document.title = to.meta.title + " | " + appName;
                    next();
                } else {
                    console.log("if your role doesn't have access to the page");
                    document.title = "Home | " + appName;
                    //if your role doesn't have access to the page
                    if(userRole != getRoleByString("examiner")){
                        console.log("Admin page");
                        next('/adminhome');
                    }else{
                        console.log("Examiner page");
                        next('/examinerhome');
                    }
                }
            }else{
                document.title = "Login | " + appName;
                next('/login');
            }
        }
    }
});
initialize(store, router);

Vue.mixin({
    methods: {
        createLog: function (user, action) {
            createLog(user, action);
        }
    }
});

const app = new Vue({
    el: '#app',
    router,
    store,
    components: {
        MainApp,
    }
});
