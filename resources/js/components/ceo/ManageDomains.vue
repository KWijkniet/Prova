<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card" v-if="viewType.length <= 0 && domainInfo == null">
                <div class="card-body">
                    <h5 class="card-title">Domains</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchDomain" placeholder="Search Domain">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Name</th>
                                    <th>
                                        Actions
                                        <i class="fas pointer fa-plus ml-2" v-on:click="getNewDomain('create')"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(domain, index) in getDomainList">
                                    <th>{{index+1}}</th>
                                    <td>{{domain.name}}</td>
                                    <td>
                                        <div class="dropdown group-dropdown">
                                            <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-boundary="viewport" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <p class="dropdown-item pointer" @click="getDomainInfo(domain, 'edit')">Edit</p>
                                                <p class="dropdown-item pointer" v-on:click="deleteDomainById(domain.id)">Delete</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card" v-if="viewType == 'create' && domainInfo != null">
                <div class="card-body">
                    <button class="btn btn-outline-primary" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                    <button class="btn btn-outline-primary" v-on:click="newDomain()"><i class="fas fa-save"></i> Save</button>
                    <h5 class="card-title mt-2">
                        <b>Create:</b>
                    </h5>
                    <div class="card-text">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Name:</span>
                            </div>
                            <input type="text" class="form-control" v-model="domainInfo.name">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" v-if="viewType == 'edit' && domainInfo != null">
                <div class="card-body">
                    <button class="btn btn-outline-primary" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                    <button class="btn btn-outline-primary" v-on:click="saveDomain()"><i class="fas fa-save"></i> Save</button>
                    <h5 class="card-title mt-2">
                        <b>Edit:</b>
                    </h5>
                    <div class="card-text">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Name:</span>
                            </div>
                            <input type="text" class="form-control" v-model="domainInfo.name">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'examcreator',
        data() {
            return {
                searchDomain: '',
                viewType: '',
                domains: [],
                error: null,
                domainInfo: null,
                canEdit: false
            };
        },

        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
            },
            getDomainList() {
                if(this.domains.length == 0) {return [];}
                const value = this.searchDomain.toLowerCase();
                return this.domains.filter(function(domain){
                    return domain.name.toLowerCase().indexOf(value) > -1
                });
            }
        },

        methods: {
            back(){
                this.$router.push('adminhome');
            },
            redirect(path, id){
                if(id == null){
                    this.$router.push(path);
                }else{
                    this.$router.push({ name: path, params: { 'id': id }});
                }
            },
            deleteDomainById(id) {
                var self = this;
                this.$dialog
                .confirm({
                    title: "Warning",
                    body: "Are you sure you want to delete the domain?",
                })
                .then(dialog => {
                    new Promise((res, rej) => {
                        axios.post('/api/ceo/deleteDomainById',{id:id})
                        .then((response) => {
                            for (var i = 0; i < this.domains.length; i++) {
                                if (this.domains[i].id === id) {
                                    this.domains.splice(i, 1);
                                }
                            }
                            self.$noty.success("Succesfully deleted domain.");
                        })
                        .catch((err) =>{
                            self.createLog(self.currentUser, `Tried to delete domain but an error occured`);
                            self.$noty.error("Cannot delete domain.");
                        })
                    });
                })
                .catch(() => {
                   return false;
                });
            },
            getDomains(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/ceo/getDomains', {id: this.currentUser.domain})
                    .then((response) => {
                        this.domains = response.data;
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to load domains but an error occured`);
                        self.$noty.error("Cannot load domains.");
                    })
                });
            },
            getDomainInfo(data, type) {
                this.viewType = type;
                this.domainInfo = data;
            },
            getNewDomain(type){
                this.viewType = type;

                this.domainInfo = {
                    'name': '',
                }
            },
            newDomain(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/ceo/newDomain', {data: this.domainInfo})
                    .then((response) => {
                        this.domains.push(response.data);
                        this.returnToList();
                        self.$noty.success("Succesfully created a new domain!");
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to create domains but an error occured`);
                        self.$noty.error("Cannot create domains.");
                    })
                });
            },
            saveDomain(){
                var self = this;
                var data = JSON.parse(JSON.stringify(this.domainInfo));
                delete data.domain;
                new Promise((res, rej) => {
                    axios.post('/api/ceo/saveDomain', {'domain': data})
                    .then((response) => {
                        for (var i = 0; i < this.domains.length; i++) {
                            if (this.domains[i].id == data.id) {
                                this.domains[i].name = data.name;
                                break;
                            }
                        }
                        self.$noty.success("Succesfully updated a domain!");
                        this.returnToList();
                    })
                    .catch((err) =>{
                        this.error = err;
                        self.$noty.error("Cannot save domain.");
                    })
                });
            },
            returnToList(){
                this.domainInfo = null;
                this.viewType = '';
            },
        },
        mounted(){
            this.getDomains();
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
