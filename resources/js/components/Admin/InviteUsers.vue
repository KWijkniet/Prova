<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card" v-if="viewType.length <= 0 && invitationInfo == null">
                <div class="card-body">
                    <h5 class="card-title" v-if="currentUser.role_id == getRoleByString('ceo')">Pending Super Admins</h5>
                    <h5 class="card-title" v-if="currentUser.role_id == getRoleByString('superAdmin')">Pending Users</h5>
                    <h5 class="card-title" v-if="currentUser.role_id == getRoleByString('admin')">Pending Examiners</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchInvite" placeholder="Search Pending User">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Email</th>
                                    <th v-if="currentUser.role_id != getRoleByString('admin')">Role</th>
                                    <th v-if="currentUser.role_id == getRoleByString('superAdmin')">Education</th>
                                    <th v-if="currentUser.role_id == getRoleByString('ceo')">Domain</th>
                                    <th>Created At</th>
                                    <th>
                                        Actions
                                        <i class="fas pointer fa-plus ml-2" v-on:click="getNewInvitation('create')"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(invitation, index) in getInvitationList">
                                    <th>{{index+1}}</th>
                                    <td>{{invitation.email}}</td>
                                    <td v-if="currentUser.role_id != getRoleByString('admin')">{{invitation.role.name}}</td>
                                    <td v-if="currentUser.role_id != getRoleByString('ceo') && currentUser.role_id != getRoleByString('admin')">{{invitation.education.name}}</td>
                                    <td v-if="currentUser.role_id == getRoleByString('ceo')">{{invitation.domain.name}}</td>
                                    <td>{{invitation.created_at}}</td>
                                    <td>
                                        <div class="dropdown group-dropdown">
                                            <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-boundary="viewport" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <p class="dropdown-item pointer" v-on:click="deleteInvitationById(invitation.id)">Delete</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card" v-if="viewType == 'create' && invitationInfo != null">
                <div class="card-body">
                    <button class="btn btn-outline-primary" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                    <button class="btn btn-outline-primary" v-on:click="newInvitation()"><i class="fas fa-save"></i> Save</button>
                    <h5 class="card-title mt-2">
                        <b>Create Invitation:</b>
                    </h5>
                    <div class="card-text">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Email:</span>
                            </div>
                            <input type="text" class="form-control" v-model="invitationInfo.email">
                        </div>
                        <div class="input-group mb-3" v-if="currentUser.role_id == getRoleByString('superAdmin')">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Role:</span>
                            </div>
                            <select class="custom-select" v-model="invitationInfo.role_id">
                                <option v-for="role in roles" v-bind:value="role.id" v-if="role.id == invitationInfo.role_id" selected>{{role.name}}</option>
                                <option v-for="role in roles" v-bind:value="role.id" v-if="role.id != invitationInfo.role_id">{{role.name}}</option>
                            </select>
                        </div>
                        <div class="input-group mb-3" v-if="currentUser.role_id == getRoleByString('superAdmin')">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Education:</span>
                            </div>
                            <select class="custom-select" v-model="invitationInfo.education_id">
                                <option v-for="education in educations" v-bind:value="education.id" v-if="education.id == invitationInfo.education_id" selected>{{education.name}}</option>
                                <option v-for="education in educations" v-bind:value="education.id" v-if="education.id != invitationInfo.education_id">{{education.name}}</option>
                            </select>
                        </div>
                        <div class="input-group mb-3" v-if="currentUser.role_id == getRoleByString('ceo')">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Domains:</span>
                            </div>
                            <select class="custom-select" v-model="invitationInfo.domain_id">
                                <option value="null" selected>-- select a Domain --</option>
                                <option v-for="domain in domains" v-bind:value="domain.id">{{domain.name}}</option>
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
                searchInvite: '',
                viewType: '',
                invitations: [],
                roles: [],
                educations: [],
                domains: [],
                error: null,
                invitationInfo: null,
                canEdit: false
            };
        },

        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
            },
            getInvitationList() {
                if(this.invitations.length == 0) {return [];}
                const value = this.searchInvite.toLowerCase();
                return this.invitations.filter(function(invite){
                    return invite.email.toLowerCase().indexOf(value) > -1
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
            deleteInvitationById(id) {
                var self = this;
                this.$dialog
                .confirm({
                    title: "Warning",
                    body: "Are you sure you want to cancel this invitation?",
                })
                .then(dialog => {
                    new Promise((res, rej) => {
                        axios.post('/api/superAdmin/deleteInvitationById',{id:id})
                        .then((response) => {
                            var data = {};
                            for (var i = 0; i < self.invitations.length; i++) {
                                if (self.invitations[i].id === id) {
                                    data = self.invitations[i];
                                    self.invitations.splice(i, 1);
                                }
                            }
                            self.$noty.info(`Successfully canceled invitation of ${data.email}`);
                            self.createLog(self.currentUser, `Canceled invitation of ${data.id}`);
                        })
                        .catch((err) => {
                            self.createLog(self.currentUser, `Tried to cancel invitation of ${data.id} but failed`);
                            self.$noty.error("Invitation cannot be deleted");
                        })
                    });
                })
                .catch((err) => {});
            },
            getInvitations(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/getInvitations', {domain_id: self.currentUser.domain_id, education_id: self.currentUser.education_id})
                    .then((response) => {
                        self.invitations = response.data;
                    })
                    .catch((err) => {
                        self.$noty.error("Could not fetch invitations");
                        self.createLog(self.currentUser, `Requested invitations but failed`);
                    })
                });
            },
            getRoles(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/getRoles', {id: self.currentUser.domain_id})
                    .then((response) => {
                        self.roles = response.data;
                    })
                    .catch((err) => {
                        self.$noty.error("Could not fetch roles");
                        self.createLog(self.currentUser, `Failed to load roles`);
                    })
                });
            },
            getEducations(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/getEducations', {id: self.currentUser.domain})
                    .then((response) => {
                        self.educations = response.data;
                    })
                    .catch((err) => {
                        self.$noty.error("Could not fetch educations");
                        self.createLog(self.currentUser, `Failed to load educations`);
                    })
                });
            },
            getDomains(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/ceo/getDomains', {id: self.currentUser.domain_id})
                    .then((response) => {
                        self.domains = response.data;
                    })
                    .catch((err) => {
                        self.$noty.error("Could not fetch domains");
                        self.createLog(self.currentUser, `Failed to load domains`);
                    })
                });
            },
            getNewInvitation(type){
                this.viewType = type;

                this.invitationInfo = {
                    'email': null,
                    'domain_id': null,
                    'education_id': null,
                    'role_id': null
                };

                if(this.currentUser.role_id == getRoleByString('ceo')){
                    this.invitationInfo['education_id'] = null;
                    this.invitationInfo['role_id'] = getRoleByString('superAdmin');
                }else if(this.currentUser.role_id == getRoleByString('superAdmin')){
                    this.invitationInfo['domain_id'] = this.currentUser.domain_id;
                }else{
                    this.invitationInfo['role_id'] = getRoleByString('examiner');
                    this.invitationInfo['domain_id'] = this.currentUser.domain_id;
                    this.invitationInfo['education_id'] = this.currentUser.education_id;
                }
            },
            newInvitation(){
                var self = this;
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if(!re.test(String(this.invitationInfo.email).toLowerCase())){
                    this.$noty.error('Invalid email address');
                    return;
                }
                if (this.invitationInfo.role_id == null) {
                    this.$noty.error('Please select a role');
                    return;
                }
                if(this.invitationInfo.education_id == null) {
                    this.$noty.error("Please select an education");
                    return;
                }
                if(this.currentUser.role_id == getRoleByString('ceo')){
                    if (this.invitationInfo.domain_id == null) {
                        this.$noty.error('Please select a domain');
                        return;
                    }
                }
                this.$dialog
                .confirm({
                    title: "Warning",
                    body: "Are you sure you want to invite this person?",
                })
                .then(dialog => {
                    new Promise((res, rej) => {
                        axios.post('/api/superAdmin/newInvitation', {data: this.invitationInfo})
                        .then((response) => {
                            self.invitations.push(response.data);
                            self.sendEmail(response.data.id);
                        })
                        .catch((err) => {
                                self.$noty.error(`${err.response.data.errors['data.email']}`);
                            self.createLog(this.currentUser, `Tried to invite a user but failed`);
                        })
                    });
                })
                .catch((err) => {});
            },
            sendEmail(id){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/sendEmail', {id: id})
                    .then((response) => {
                        self.$noty.success(`Successfully invited ${this.invitationInfo.email}`);
                        self.createLog(this.currentUser, `Invited a user with the invitation id: ${id}`);
                        self.returnToList();
                    })
                    .catch((err) => {
                        self.$noty.error(`Couldn't send an email to ${this.invitationInfo.email}`);
                        self.createLog(self.currentUser, `Failed to send email`);
                    })
                });
            },
            returnToList(){
                this.invitationInfo = null;
                this.viewType = '';
            },
        },
        mounted(){
            this.getInvitations();
            this.getRoles();
            this.getEducations();
            if(this.currentUser.role_id == getRoleByString('ceo')){
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
