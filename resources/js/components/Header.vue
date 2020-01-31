<template>
    <nav class="navbar navbar-expand-lg navbar-light navbar-laravel" v-if="currentUser != null">

        <router-link class="navbar-brand" to="/examinerhome">Examen Afname</router-link>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li>
                    <router-link to="/examinerhome" class="nav-link" v-if="hasAccess('/examinerhome')">Home</router-link>
                </li>
                <li>
                    <router-link to="/adminhome" class="nav-link" v-if="hasAccess('/adminhome')">Home</router-link>
                </li>
                <li>
                    <router-link to="/students" class="nav-link" v-if="hasAccess('/students')">Students</router-link>
                </li>
                <li>
                    <a href="#" @click.prevent="logout" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
    import { getRoleByString } from "../helpers/roles";
    export default {
        name: 'app-header',
        data() {
            return {
                error: ""
            };
        },
        methods: {
            hasAccess(path){
                var foundRoles = this.$router.resolve({path: path}).route.meta.roles;
                var roles = [];
                for (var i = 0; i < foundRoles.length; i++) {
                    roles[i] = getRoleByString(foundRoles[i]);
                }

                if(roles.length <= 0 || roles.includes(this.currentUser.role_id)){
                    //has access
                    return true;
                }
                return false;
            },
            logout(showNotification = false) {
                if(this.currentUser != null){
                    this.currentUser = null;
                    this.$store.commit('logout');
                    this.$router.push('/login');
                }
            },
            checkSession(){
                var self = this;
                this.$store.state.isLoading = true;
                new Promise((res, rej) => {
                    axios.post('/api/auth/getSession', {'userId': this.$store.state.currentUser.id})
                    .then((response) => {
                        this.$store.state.isLoading = false;
                        var session = response.data;
                        if(new Date(session.expiration_date) < new Date()){
                            self.$store.commit('logout');
                            this.$router.push({path: '/login'});
                        }else{
                            setInterval(function() {
                                var cookie = localStorage.getItem("session");
                                if (!cookie && self.$store.state.currentUser) {
                                    self.$store.commit('logout');
                                    this.$router.push({path: '/login'});
                                }
                            }, 60000); //1 minute

                            setInterval(function(){
                                if(self.$store.state.currentUser){
                                    self.$store.commit('updateSession');
                                }
                            }, (1000 * 60 * 50 /* 50 minutes */));
                        }
                    })
                    .catch((err) => {
                        this.$noty.error(err.response.data.message)
                    })
                });
            },
            checkActivity(){
                let self = this;
                var idleTime = 0;
                var idleInterval = setInterval(timerIncrement, 60000); //1 minute

                document.addEventListener("mousemove", resetTimer, false);
                document.addEventListener("mousedown", resetTimer, false);
                document.addEventListener("keypress", resetTimer, false);
                document.addEventListener("DOMMouseScroll", resetTimer, false);
                document.addEventListener("mousewheel", resetTimer, false);
                document.addEventListener("touchmove", resetTimer, false);
                document.addEventListener("MSPointerMove", resetTimer, false);

                function resetTimer(){
                    idleTime = 0;
                }

                function timerIncrement() {
                    if(self.$store.state.currentUser != null){
                        idleTime++;
                        if (idleTime >= 120) { //minutes afk before auto logout
                            self.logout(true);
                            clearInterval(idleInterval);
                        }
                    }
                }
            },
            updateData(){
                if(this.$store.state.currentUser != null && localStorage.getItem("session")){
                    this.checkSession();
                    this.checkActivity();
                }
            }
        },
        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
                set: function () {
                }
            }
        },
        mounted(){
            this.updateData();
        }
    }
</script>
