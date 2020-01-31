<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card" v-if="viewType.length <= 0 && studentInfo == null">
                <!-- LIST -->
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend" style="max-width: 50px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchVal" placeholder="Search Student">
                        <select class="custom-select col-md-2" style="border: none;" v-model="educationId" v-if="canEdit">
                            <option value="-1" selected>None</option>
                            <option v-for="(education, index) in educations" v-bind:value="education.id">{{education.name}}</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Name</th>
                                    <th>Domain</th>
                                    <th>Education</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(student, index) in getStudentsList">
                                    <th>{{index+1}}</th>
                                    <td>
                                        {{student.name}}
                                        <span>{{student.year}}</span>
                                        <br />
                                        <span>{{student.ov}}</span>
                                    </td>
                                    <td>{{student.domain.name}}</td>
                                    <td>{{student.education.name}}</td>
                                    <td>
                                        <i class="pointer fas fa-eye mr-2" v-on:click="getStudentInfo(student.id, 'view')"></i>
                                        <i class="pointer fas fa-pen" v-if="canEdit" v-on:click="getStudentInfo(student.id, 'edit')"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View -->
            <div v-if="viewType == 'view' && studentInfo != null">
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-outline-primary" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                        <h5 class="card-title mt-2">
                            <b>{{studentInfo.name}}</b>
                        </h5>
                        <p class="card-text">
                            Student Number: {{studentInfo.ov}} <br />
                            Start Date: {{studentInfo.year}} <br />
                            Education: {{studentInfo.education.name}} <br />
                        </p>
                    </div>
                </div>
                <div class="card mt-2">
                    <div class="card-body">
                        <h5 class="card-title">Previous Exams</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-large">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">#</th>
                                        <th>Name</th>
                                        <th>Mark</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(result, index) in studentInfo.results">
                                        <th>{{index+1}}</th>
                                        <td>{{result.examName}}</td>
                                        <td>{{result.mark}}</td>
                                        <td>{{result.creation_date}}</td>
                                        <td>
                                            <i class="pointer fas fa-eye mr-2" v-on:click="viewExam(result.id)"></i>
                                        </td>
                                    </tr>
                                    <tr v-if="studentInfo.results.length <= 0">
                                        <th>-</th>
                                        <td>No Exams Found</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EDIT -->
            <div class="card" v-if="viewType == 'edit' && studentInfo != null">
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
                            <input type="text" class="form-control" v-model="studentInfo.name">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">OV:</span>
                            </div>
                            <input type="text" class="form-control" v-model="studentInfo.ov">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Year:</span>
                            </div>
                            <input type="text" class="form-control" v-model="studentInfo.year">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Education:</span>
                            </div>
                            <select class="custom-select" v-model="studentInfo.education_id" v-if="currentUser.role_id < getRoleByString('admin')">
                                <option v-for="education in educations" v-bind:value="education.id" v-if="education.id == studentInfo.education_id" selected>{{education.name}}</option>
                                <option v-for="education in educations" v-bind:value="education.id" v-if="education.id != studentInfo.education_id">{{education.name}}</option>
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
    data() {
        return {
            educations: [],
            students: [],
            studentInfo: null,
            viewType: '',
            searchVal: '',
            educationId: -1,
            error: '',
            canEdit: false
        }
    },
    computed: {
        getStudentsList() {
            if(this.students.length == 0) { return []; }
            var self = this;

            const value = this.searchVal.toLowerCase();
            return this.students.filter(function(student){
                if(self.educationId != -1){
                    return student.education_id == self.educationId;
                }else{
                    return student.name.toLowerCase().indexOf(value) > -1 ||
                        student.year.toLowerCase().indexOf(value) > -1 ||
                            student.ov.toLowerCase().indexOf(value) > -1;
                }
            });
        },
        currentUser: {
            get: function () {
                return this.$store.state.currentUser;
            },
            set: function () {
            }
        }
    },
    methods: {
        back(){
            this.$router.push('adminhome');
        },
        getRoleByString(name){
            return getRoleByString(name);
        },
        saveEdit(){
            var self = this;
            if(getRoleByString("examiner") == this.currentUser.role_id){ return; }
            var data = JSON.parse(JSON.stringify(this.studentInfo));
            delete data.education;
            delete data.results;
            new Promise((res, rej) => {
                axios.post('/api/students/saveStudent', {'student': data})
                .then((response) => {
                    for (var i = 0; i < this.students.length; i++) {
                        if(this.students[i].id == response.data.id){
                            this.students[i] = response.data;
                        }
                    }
                    this.returnToList();
                    self.$noty.success("Succesfully saved student!");
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to save edit but an error occured`);
                    self.$noty.error("Cannot save edit.");
                })
            });
        },
        returnToList(){
            this.studentInfo = null;
            this.viewType = '';
        },
        viewExam(id){
            this.$router.push({ name: 'StudentResult', params: { 'resultId': id }});
        },
        getStudentInfo(id, type) {
            this.viewType = type;
            for (var i = 0; i < this.students.length; i++) {
                if(this.students[i]['id'] == id){
                    this.studentInfo = this.students[i];
                }
            }
        },
        getStudents() {
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/students/getStudents', {'education_id': this.currentUser.education_id, 'domain_id': this.currentUser.domain_id})
                .then((response) => {
                    this.students = response.data;
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to get students but an error occured`);
                    self.$noty.error("Cannot get students");
                })
            });
        },
        getEducations() {
            new Promise((res, rej) => {
                axios.post('/api/students/geteducations', {'domain_id': this.currentUser.domain_id})
                .then((response) => {
                    this.educations = response.data;
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to get educations but an error occured`);
                    self.$noty.error("Cannot get educations");
                })
            });
        }
    },
    mounted() {
        this.getStudents();
        this.getEducations();
        if(getRoleByString("examiner") != this.currentUser.role_id){
            this.canEdit = true;
        }
    }
}
</script>
<style scoped>
table { table-layout: fixed; }
table th, table td { overflow: hidden; }

td span{
    color: rgba(0,0,0,0.65);
    font-size: 0.85em;
}

.input-group-text{
    min-width: 100px;
}

.card{
    width: 100%;
}
</style>
