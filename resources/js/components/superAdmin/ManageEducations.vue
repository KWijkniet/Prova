<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card" v-if="viewType.length <= 0 && educationInfo == null">
                <div class="card-body">
                    <h5 class="card-title">Educations</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchEducation" placeholder="Search Education">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Name</th>
                                    <th>Domain</th>
                                    <th>
                                        Actions
                                        <i class="fas pointer fa-plus ml-2" v-on:click="getNewEducation('create')"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(education, index) in getEducationList">
                                    <th>{{index+1}}</th>
                                    <td>{{education.name}}</td>
                                    <td>{{education.domain.name}}</td>
                                    <td>
                                        <div class="dropdown group-dropdown">
                                            <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-boundary="viewport" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <p class="dropdown-item pointer" v-on:click="getEducationInfo(education, 'edit')">Edit</p>
                                                <p class="dropdown-item pointer" v-on:click="deleteEducationById(education.id)">Delete</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card" v-if="viewType == 'create' && educationInfo != null">
                <div class="card-body">
                    <button class="btn btn-outline-primary" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                    <button class="btn btn-outline-primary" v-on:click="newEducation()"><i class="fas fa-save"></i> Save</button>
                    <h5 class="card-title mt-2">
                        <b>Create:</b>
                    </h5>
                    <div class="card-text">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Name:</span>
                            </div>
                            <input type="text" class="form-control" v-model="educationInfo.name">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" v-if="viewType == 'edit' && educationInfo != null">
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
                            <input type="text" class="form-control" v-model="educationInfo.name">
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
                searchEducation: '',
                viewType: '',
                educations: [],
                error: null,
                educationInfo: null,
                canEdit: false
            };
        },

        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
            },
            getEducationList() {
                if(this.educations.length == 0) {return [];}
                const value = this.searchEducation.toLowerCase();
                return this.educations.filter(function(education){
                    return education.name.toLowerCase().indexOf(value) > -1
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
            deleteEducationById(id) {
                var self = this;
                this.$dialog
                .confirm({
                    title: "Warning",
                    body: "Are you sure you want to delete this education?",
                })
                .then(dialog => {
                    new Promise((res, rej) => {
                        axios.post('/api/superAdmin/deleteEducationById',{id:id})
                        .then((response) => {
                            for (var i = 0; i < this.educations.length; i++) {
                                if (this.educations[i].id === id) {
                                    this.educations.splice(i, 1);
                                }
                            }
                            self.$noty.success("Succesfully deleted education!");
                        })
                        .catch((err) => {
                            self.$noty.error("Education cannot be deleted");
                        })
                    });
                })
                .catch(() => {});
            },
            getEducations(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/getEducations', {id: this.currentUser.domain})
                    .then((response) => {
                        this.educations = response.data;
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to get educations but an error occured`);
                        self.$noty.error("Cannot get educations.");
                    })
                });
            },
            getEducationInfo(data, type) {
                this.viewType = type;
                this.educationInfo = data;
            },
            getNewEducation(type){
                this.viewType = type;

                this.educationInfo = {
                    'name': '',
                    'domain_id': this.currentUser.domain_id
                }
            },
            newEducation(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/newEducation', {data: this.educationInfo})
                    .then((response) => {
                        this.educations.push(response.data);
                        self.$noty.success("Succesfully created an education!");
                        this.returnToList();
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to create an education but an error occured`);
                        self.$noty.error("Cannot create a new education.");
                    })
                });
            },
            saveEdit(){
                var self = this;
                var data = JSON.parse(JSON.stringify(this.educationInfo));
                delete data.education;
                new Promise((res, rej) => {
                    axios.post('/api/superAdmin/saveEducation', {'education': data})
                    .then((response) => {
                        for (var i = 0; i < this.educations.length; i++) {
                            if (this.educations[i].id == data.id) {
                                this.educations[i].name = data.name;
                                this.educations[i].domain = this.currentUser.domain;
                                break;
                            }
                        }
                        self.$noty.success("Succesfully saved edit!");
                        this.returnToList();
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to save edit but an error occured`);
                        self.$noty.error("Cannot save edit.");
                    })
                });
            },
            returnToList(){
                this.educationInfo = null;
                this.viewType = '';
            },
        },
        mounted(){
            this.getEducations();
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
