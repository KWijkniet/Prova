<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card" v-if="viewType.length <= 0 && adminInfo == null">
                <div class="card-body">
                    <h5 class="card-title" v-if="currentUser.role_id == getRoleByString('ceo')">Super Admins</h5>
                    <h5 class="card-title" v-if="currentUser.role_id != getRoleByString('ceo') && currentUser.role_id != getRoleByString('admin')">Users</h5>
                    <h5 class="card-title" v-if="currentUser.role_id == getRoleByString('admin')">Examiners</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchAdmin" placeholder="Search Admin">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th v-if="currentUser.role_id != getRoleByString('ceo')">Education</th>
                                    <th>Domain</th>
                                    <th>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(admin, index) in getAdminList">
                                    <th>{{index+1}}</th>
                                    <td>{{admin.username}}</td>
                                    <td>{{admin.role.name}}</td>
                                    <td v-if="currentUser.role_id != getRoleByString('ceo')">{{admin.education.name}}</td>
                                    <td>{{admin.domain.name}}</td>
                                    <td>
                                        <div class="dropdown group-dropdown">
                                            <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-boundary="viewport" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <p class="dropdown-item pointer" v-on:click="getAdminInfo(admin, 'edit')">Edit</p>
                                                <p class="dropdown-item pointer" v-on:click="deleteAdminById(admin.id)">Delete</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card" v-if="viewType == 'edit' && adminInfo != null">
                <div class="card-body">
                    <button class="btn btn-outline-primary" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                    <button class="btn btn-outline-primary" v-on:click="saveEdit()"><i class="fas fa-save"></i> Save</button>
                    <h5 class="card-title mt-2">
                        <b>Edit:</b>
                    </h5>
                    <div class="card-text">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Name:</span>
                            </div>
                            <input type="text" class="form-control" v-model="adminInfo.username">
                        </div>
                        <div class="input-group mb-3" v-if="currentUser.role_id != getRoleByString('ceo')">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Education:</span>
                            </div>
                            <select class="custom-select" v-model="adminInfo.education_id">
                                <option v-for="education in educations" v-bind:value="education.id" v-if="education.id == adminInfo.education_id" selected>{{education.name}}</option>
                                <option v-for="education in educations" v-bind:value="education.id" v-if="education.id != adminInfo.education_id">{{education.name}}</option>
                            </select>
                        </div>
                        <div class="input-group mb-3" v-if="currentUser.role_id == getRoleByString('ceo')">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Domain:</span>
                            </div>
                            <select class="custom-select" v-model="adminInfo.domain_id">
                                <option v-for="domain in domains" v-bind:value="domain.id" v-if="domain.id == adminInfo.domain_id" selected>{{domain.name}}</option>
                                <option v-for="domain in domains" v-bind:value="domain.id" v-if="domain.id != adminInfo.domain_id">{{domain.name}}</option>
                            </select>
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
        name: 'examcreator',
        data() {
            return {
                searchAdmin: '',
                viewType: '',
                admins: [],
                educations: [],
                domains: [],
                error: null,
                adminInfo: null,
                canEdit: false
            };
        },

        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
            },
            getAdminList() {
                if(this.admins.length == 0) {return [];}
                const value = this.searchAdmin.toLowerCase();
                return this.admins.filter(function(admin){
                    return admin.username.toLowerCase().indexOf(value) > -1
                });
            }
        },

        methods: {
            back(){
                this.$router.push('adminhome');
            },
            getRoleByString(name){
                return getRoleByString(name);
            },
            redirect(path, id){
                if(id == null){
                    this.$router.push(path);
                }else{
                    this.$router.push({ name: path, params: { 'id': id }});
                }
            },
            deleteAdminById(id) {
                var self = this;
                self.$dialog
                .confirm({
                    title: "Warning",
                    body: "Are you sure you want to delete this user?",
                })
                .then(dialog => {
                    new Promise((res, rej) => {
                        axios.post('/api/superAdmin/deleteAdminById',{id:id})
                        .then((response) => {
                            for (var i = 0; i < self.admins.length; i++) {
                                if (self.admins[i].id === id) {
                                    self.admins.splice(i, 1);
                                }
                            }
                            self.$noty.success(`Successfully deleted an admin`);
                        })
                        .catch((err) =>{
                            self.$noty.error("An error occured");
                        })
                    });
                })
                .catch((err) => {});


            },
            getUsers(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/getUsers', {id: this.currentUser.domain_id, role_id: this.currentUser.role_id})
                    .then((response) => {
                        this.admins = response.data;
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to load admins but an error occured`);
                        self.$noty.error("Cannot get admins.");
                    })
                });
            },
            getDomains(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/ceo/getDomains', {id: this.currentUser.domain_id})
                    .then((response) => {
                        this.domains = response.data;
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to load domains but an error occured`);
                        self.$noty.error("Cannot get domains.");
                    })
                });
            },
            getAdminInfo(data, type) {
                this.viewType = type;
                this.adminInfo = data;
            },
            getEducations(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/getEducations', {id: this.currentUser.domain})
                    .then((response) => {
                        this.educations = response.data;
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to load educations but an error occured`);
                        self.$noty.error("Cannot get educations.");
                    })
                });
            },
            saveEdit(){
                var data = JSON.parse(JSON.stringify(this.adminInfo));
                delete data.domain;
                delete data.education;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/saveAdmin', {'admin': data})
                    .then((response) => {
                        for (var i = 0; i < this.admins.length; i++) {
                            if (this.admins[i].id == data.id) {
                                this.admins[i].username = data.username;

                                if(this.currentUser.role_id != getRoleByString("ceo")){
                                    for (var r = 0; r < this.educations.length; r++) {
                                        if (this.educations[r].id == data.education_id) {
                                            this.admins[i]['education'] = this.educations[r];
                                            break;
                                        }
                                    }
                                }
                                else{
                                    for (var r = 0; r < this.domains.length; r++) {
                                        if (this.domains[r].id == data.domain_id) {
                                            this.admins[i]['domain'] = this.domains[r];
                                            break;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                        this.returnToList();
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to save admin but an error occured`);
                        self.$noty.error("Cannot save admin.");
                    })
                });
            },
            returnToList(){
                this.adminInfo = null;
                this.viewType = '';
            },
        },
        mounted(){
            this.getUsers();
            this.getEducations();
            if(this.currentUser.role_id == getRoleByString("ceo")){
                this.getDomains();
            }
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
</style>
