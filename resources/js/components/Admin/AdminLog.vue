<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Admin Log</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchLog" placeholder="Search Log" v-bind:onchange="filterChange()">
                        <select class="custom-select col-md-2" style="border: none;" v-model="searchUser" v-bind:onchange="filterChange()">
                            <option value="-1" selected>None</option>
                            <option v-for="(user, index) in users" v-bind:value="user.id">{{user.name}}</option>
                        </select>
                        <select class="custom-select col-md-2" style="border: none;" v-model="searchRole" v-bind:onchange="filterChange()">
                            <option value="-1" selected>None</option>
                            <option v-for="(role, index) in roles" v-bind:value="role.id">{{role.name}}</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, index) in getLog">
                                    <th>{{index+1 + (logsPerPage * page)}}</th>
                                    <td>{{record.username}}</td>
                                    <td>{{record.role.name}}</td>
                                    <td>{{record.action}}</td>
                                    <td>{{record.created_at}}</td>
                                </tr>
                                <tr v-if="getLog.length <= 0">
                                    <th>-</th>
                                    <td>-</td>
                                    <td>No Logs Found</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-outline-primary mt-2" v-on:click="prevPage()" v-bind:disabled="disablePrev">prev</button>
                    <button class="btn btn-outline-primary mt-2" v-on:click="nextPage()" v-bind:disabled="disableNext">next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { getRoleByString } from "../../helpers/roles";
    export default {
        name: 'Log',
        data() {
            return {
                logs: [],
                users: [],
                roles: [],
                page: 0,
                logsPerPage: 15,
                disablePrev: true,
                disableNext: true,
                searchLog: "",
                searchUser: -1,
                searchRole: -1,
                error: ""
            };
        },

        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                }
            },
            getLog: {
                get: function () {
                    if(this.logs.length == 0) { return []; }
                    var logs = [];
                    const text = this.searchLog.toLowerCase();
                    const role = parseInt(this.searchRole);
                    const user = parseInt(this.searchUser);

                    logs = this.logs.filter(function(log){
                        if(text.length <= 0){
                            return true;
                        }
                        return log.username.toLowerCase().indexOf(text) > -1 || log.role.name.toLowerCase().indexOf(text) > -1 || log.action.toLowerCase().indexOf(text) > -1
                    });
                    logs = logs.filter(function(log){
                        if(role <= -1){
                            return true;
                        }
                        return log.role_id == role
                    });
                    logs = logs.filter(function(log){
                        if(user <= -1){
                            return true;
                        }
                        return log.user_id == user
                    });

                    if(logs.length >= this.page * this.logsPerPage){
                        if(logs.length >= (this.page + 1) * this.logsPerPage){
                            return logs.slice(this.page * this.logsPerPage, (this.page + 1) * this.logsPerPage);
                        }else{
                            return logs.slice(this.page * this.logsPerPage, logs.length);
                        }
                        this.page++;
                    }else{
                        return [];
                    }
                }
            }
        },
        methods: {
            back(){
                this.$router.go(-1);
            },
            getLogs() {
                var self = this;
                new Promise((res, rej) => {
                    axios.post("/api/log/getLog", {id:this.currentUser.id})
                    .then((response) => {
                        this.logs = response.data;
                        if(this.logs.length > this.logsPerPage){
                            this.disableNext = false;
                        }
                        var roles = [];
                        var users = [];
                        for(var i = 0; i < this.logs.length; i++){
                            var log = this.logs[i];

                            var hasRole = false;
                            for(var r = 0; r < roles.length; r++){
                                if(roles[r].id == log.role_id){
                                    hasRole = true;
                                }
                            }
                            if(!hasRole){
                                roles.push({id:log.role_id, name: log.role.name});
                            }

                            var hasUser = false;
                            for(var r = 0; r < users.length; r++){
                                if(users[r].id == log.user_id){
                                    hasUser = true;
                                }
                            }
                            if(!hasUser){
                                users.push({id:log.user_id, name: log.username});
                            }
                        }
                        this.roles = roles;
                        this.users = users;
                    })
                    .catch((err) =>{
                        self.createLog(this.currentUser, "Requested log but failed");
                        self.$noty.error("Error occured while fetching log");
                    })
                });
            },
            nextPage(){
                this.page++;
                this.checkButtons();
            },
            prevPage(){
                this.page--;
                this.checkButtons();
            },
            checkButtons(){
                if(this.page <= 0){
                    this.disablePrev = true;
                }else{
                    this.disablePrev = false;
                }

                if(this.logs.length < (this.page + 1) * this.logsPerPage){
                    this.disableNext = true;
                }else{
                    this.disableNext = false;
                }
            },
            filterChange(){
                this.page = 0;
                this.checkButtons();
            }
        },
        mounted(){
            this.getLogs();
        },
    }
</script>

<style scoped>
.card{
    width: 100%;
}
</style>


<!-- <template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Admin Log</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, index) in log.data">
                                    <th>{{index+1}}</th>
                                    <td>{{record.username}}</td>
                                    <td>{{record.role.name}}</td>
                                    <td>{{record.action}}</td>
                                    <td>{{record.created_at}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pagination">
                            <button type="button" class="btn btn-default" @click="fetchPaginateLog(pagination.prev_page_url)" :disabled="!pagination.prev_page_url">Previous</button>
                            <span>Page {{pagination.current_page}} of {{pagination.last_page}}</span>
                            <button type="button" class="btn btn-default" @click="fetchPaginateLog(pagination.next_page_url)" :disabled="!pagination.next_page_url">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { getRoleByString } from "../../helpers/roles";
    export default {
        name: 'Log',
        data() {
            return {
                searchLog: '',
                error: null,
                url: '/api/log/getLog',
                pagination: [],
                log: [],
            };
        },

        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
            }
        },

        methods: {
            getLog() {
                var self = this;
                new Promise((res, rej) => {
                    axios.post(this.url, {id:this.currentUser.id})
                    .then((response) => {
                        this.log = response.data;
                        // this.createLog(this.currentUser, "Requested log");
                        self.makePagination(response.data);
                    })
                    .catch((err) =>{
                        self.createLog(this.currentUser, "Requested log but failed");
                        self.$noty.error("Error occured while fetching log");
                    })
                });
            },
            makePagination(data) {
                let pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    next_page_url: data.next_page_url,
                    prev_page_url: data.prev_page_url
                }
                this.pagination = pagination;
            },
            fetchPaginateLog(url) {
                this.url = url;
                this.getLog();
            },
            back(){
                this.$router.push('adminhome');
            },
        },
        mounted(){
            this.getLog();
        },
    }
</script>

<style scoped>

.dropdown-item{
    margin-bottom: 0px;
}

.card{
    width: 100%;
}
</style> -->
