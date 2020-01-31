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
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchVal" placeholder="Search Student">
                        <select class="custom-select col-md-2" style="border: none;" v-model="educationId">
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
                                        <i class="pointer fas fa-pen" v-on:click="getStudentInfo(student.id, 'plan')"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PLAN -->
            <div class="card mt-2" v-if="viewType == 'plan' && studentInfo != null">
                <div class="card-body">
                    <button class="btn btn-outline-primary mt-2" v-on:click="returnToList()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                    <h5 class="card-title">Planned Exams</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>
                                        Actions
                                        <i class="pointer fas fa-plus mr-2" v-on:click="createNewPlanned()"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="studentInfo.planned.length <= 0">
                                    <th>-</th>
                                    <td>No Planned Exams Found</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr v-for="(planned, index) in studentInfo.planned">
                                    <th>{{index+1}}</th>
                                    <td v-for="exam in exams" v-if="exam.id == planned.exam_id">{{exam.name}}</td>
                                    <td>{{planned.date}}</td>
                                    <td>
                                        <i class="pointer fas fa-trash mr-2" v-on:click="deletePlannedExamById(index, planned.id)"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card mt-2" v-if="viewType == 'plan' && studentInfo != null && showNewPlanned">
                <div class="card-body">
                    <button class="btn btn-outline-primary mt-2" v-on:click="cancelNewPlanned()"><i class="fas fa-long-arrow-alt-left"></i> Cancel</button>
                    <button class="btn btn-outline-primary mt-2" v-on:click="saveNewPlanned()"><i class="fas fa-save"></i> Save</button>
                    <h5 class="card-title mt-2">New Planned Exam</h5>
                    <div class="card-text">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Date:</span>
                            </div>
                            <input type="date" class="form-control" id="date" onkeydown="return false" v-model="plannedData.date">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Exams:</span>
                            </div>
                            <select class="custom-select" v-model="plannedData.exam_id">
                                <option value="0" selected>-- Select An Exam --</option>
                                <option v-for="exam in exams" v-bind:value="exam.id">{{exam.name}}</option>
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
            exams: [],
            studentInfo: null,
            plannedData: null,
            educationId: -1,
            viewType: '',
            searchVal: '',
            error: '',
            showNewPlanned: false,
            todayDate: "2000-00-00"
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
        createNewPlanned(){
            this.showNewPlanned = true;
            this.plannedData = {
                'student_id': this.studentInfo.id,
                'exam_id': 0,
                'date': null
            };
            this.getToday();
        },
        saveNewPlanned(){
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/students/createPlannedExam', {'student_id': this.plannedData.student_id, 'exam_id': this.plannedData.exam_id, 'date': this.plannedData.date})
                .then((response) => {
                    for (var i = 0; i < self.students.length; i++) {
                        if(self.students[i].id == self.studentInfo.id){
                            self.students[i].planned.push(response.data);
                        }
                    }
                    self.cancelNewPlanned();
                    self.$noty.success("Saved planned date");
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to save planned exam but an error occured`);
                    self.$noty.error("Cannot save planned date.");
                })
            });
        },
        deletePlannedExamById(index, id){
            var self = this;
            this.$dialog
            .confirm({
                title: "Warning",
                body: "Are you sure you want to delete this planned exam?",
            })
            .then(dialog => {
                new Promise((res, rej) => {
                    axios.post('/api/students/deletePlannedExamById', {'id': id})
                    .then((response) => {
                        self.studentInfo.planned.splice(index, 1);
                    })
                    .catch((err) => {
                        self.createLog(self.currentUser, `Tried to delete planned exam but an error occured`);
                        self.$noty.error("Cannot delete planned date.");
                    })
                })
            })
            .catch((err) => {});
        },
        returnToList(){
            this.studentInfo = null;
            this.plannedData = null;
            this.viewType = '';
        },
        cancelNewPlanned(){
            this.plannedData = null;
            this.showNewPlanned = false;
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
                    self.students = response.data;
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to load students but an error occured`);
                    self.$noty.error("Cannot get students.");
                })
            });
        },
        getEducations() {
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/students/geteducations', {'domain_id': this.currentUser.domain_id})
                .then((response) => {
                    self.educations = response.data;
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to load educations but an error occured`);
                    self.$noty.error("Cannot get educations.");
                })
            });
        },
        getExams() {
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/students/getexams', {'domain_id': this.currentUser.domain_id, 'education_id': this.currentUser.education_id})
                .then((response) => {
                    self.exams = response.data;
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to load exams but an error occured`);
                    self.$noty.error("Cannot get exams.");
                })
            });
        },
        getToday() {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();
            if(dd<10){
                dd='0'+dd
            }
            if(mm<10){
                mm='0'+mm
            }
            today = yyyy+'-'+mm+'-'+dd;
            this.todayDate = today;
            this.plannedData.date = today;
            this.$nextTick().then(
                function() {
                    document.getElementById("date").setAttribute("min", today);
                }
            );
        },
    },
    mounted() {
        this.getStudents();
        this.getEducations();
        this.getExams();
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
::-webkit-datetime-edit,
    ::-webkit-datetime-edit-fields-wrapper,
    ::-webkit-datetime-edit-text,
    ::-webkit-datetime-edit-month-field,
    ::-webkit-datetime-edit-day-field,
    ::-webkit-datetime-edit-year-field,
    ::-webkit-inner-spin-button,
    ::-webkit-calendar-picker-indicator {
      -webkit-appearance: none;
    }
</style>
