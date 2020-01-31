<template>
    <div class="container">
        <div>
            <div class="tableBackground">
                <h4>Educations:</h4>
                <div class="table-responsive" style="max-height:250px; overflow-y: auto;">
                    <table class="table table-borderless">
                        <tr>
                            <th>#</th>
                            <th>name</th>
                            <th>actions</th>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Add new Education</td>
                            <td><i class="fas fa-plus" @click="newEducation()"></i></td>
                        </tr>
                        <tr v-for="(education, index) in educations">
                            <td>{{index + 1}}</td>
                            <td>{{education.name}}</td>
                            <td>
                                <i class="fas fa-pen" @click="editEducation(education)"></i>
                                <i class="fas fa-trash" @click="deleteEducationById(education.id)"></i>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="table-responsive tableBackground" v-if="editEducationData != null">
                <table class="table table-borderless">
                    <tr v-if="editEducationToggle && editEducationData != null">
                        <td>{{editEducationData.id}}</td>
                        <td>
                            <input type="text" v-model="editEducationData.name">
                        </td>
                        <td>
                            <button type="button" name="button" @click="confirmEditEducation()">save</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- invite a new user -->
        <div>
            <!-- add email -->
            <h4>Add User:</h4>
            <input type="email" name="" v-model="invitationInfo.email" placeholder="examiner@mail.com">
        </div>
        <div>
            <!-- choose role -->
            <h4>Choose role:</h4>
            <input type="radio" id="admin" value="1" v-model="invitationInfo.role">
            <label for="admin">admin</label>
            <br>
            <input type="radio" id="examiner" value="2" v-model="invitationInfo.role">
            <label for="examiner">examiner</label>
        </div>
        <button type="button" name="button" @click="inviteUser()">Send Request</button>

        <!-- show a list of invited users -->
        <h4>Invitations List</h4>
        <table class="table table-borderless">
            <tr>
                <th>email</th>
                <th>created_at</th>
                <th>cancel</th>
            </tr>
            <tr v-for="(invitation, index) in invitations">
                <th>{{invitation.email}}</th>
                <th>{{invitation.created_at}}</th>
                <th @click="cancelInvitation(invitation);"><i class="fas fa-ban"></i></th>
            </tr>
        </table>

        <h4>Exams:</h4>
        <input type="text" class="form-control" v-model="searchExam" placeholder="Search Exam">
        <table class="table table-borderless">
            <tr>
                <th style="width: 10px;">#</th>
                <th style="width: 40px;">Year</th>
                <th style="width: calc(100%-120px);">Name</th>
                <th style="width: 20px">state</th>
                <th style="width: 50px;">Actions</th>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <router-link to="/examcreator" class="fas fa-plus"></router-link>
                </td>
            </tr>
            <tr v-for="(exam, index) in getExamList">
                <td>{{index+1}}</td>
                <td>{{exam.year}}</td>
                <td>{{exam.name}}</td>
                <td>
                    <div v-for="state in exam_states">
                        <p v-if="state.id == exam.exam_states_id">
                            {{state.name}}
                        </p>
                    </div>
                </td>
                <td>
                    <router-link :to="{ name: 'ExamEditor', params: { 'id': exam.id }}" class="fas fa-pen" v-if="exam.exam_states_id != 4"></router-link>
                    <i class="fas fa-pen" v-else style="color:grey;"></i>
                    <i class="fas fa-copy" @click="copyExamById(exam.id, exam.name)"></i>
                    <i class="fas fa-trash" @click="deleteExamById(exam.id)"></i>
                </td>
            </tr>
        </table>
        <div class="panel panel-sm">
            <div class="panel-heading">
                <h4>Students Upload</h4>
            </div>
            <div class="panel-body">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="csv_file" class="control-label col-sm-4 text-right">Please select a csv file containing a students list.</label>
                        <div class="col-sm-9">
                            <input type="file" id="csv_file" name="csv_file" class="form-control" @change="loadCSV($event)" accept=".csv">
                        </div>
                    </div>
                    <button type="button" name="button" v-on:click="uploadConfirm" v-if="parse_csv.length > 0">Confirm</button>
                    <select v-if="parse_csv.length > 0" style="width: 100px;" v-model="selectedEducationId">
                        <option v-for="education in educations" :value="education.id">{{education.name}}</option>
                    </select>
                    <table v-if="parse_csv" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th v-for="key in parse_header" :class="{ active: sortKey == key }">
                                    {{ key | capitalize }}
                                    <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'">
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tr v-for="csv in parse_csv">
                            <td v-for="key in parse_header">
                            {{csv[key]}}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="tableBackground">
            <h4>Students</h4>
            <div v-if="getStudent == null">
                <div v-if="studentsList.length === 0">
                    <p>There are no students yet.</p>
                </div>
                <div v-else class="container" style="max-height:300px; overflow-y:auto;">
                    <div class="panel-body">
                        <div class="table-responsive" >
                            <div class="input-group sticky-top">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" v-model="searchStudent" placeholder="Search Student">
                            </div>
                            <table class="table table-bordered table-striped" >
                                <tr>
                                    <th>Student Number</th>
                                    <th>Name</th>
                                    <th>Year</th>
                                    <th>Education</th>
                                    <th>Actions</th>
                                </tr>
                                <tr v-for="(student, index) in getStudentList">
                                    <td>{{student.ov}}</td>
                                    <td>{{student.name}}</td>
                                    <td>{{student.year}}</td>
                                    <td>{{student.education.name}}</td>
                                    <td>
                                        <i @click="getStudentInfo(student); getPlannedExamsOfStudent(student.id);" class="fa fas fa-pen"></i>
                                        <i @click="deleteStudentById(student.id);" class="fa fas fa-trash"></i>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tableBackground" v-if="getStudent != null">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tr>
                        <td @blur="updateStudentOv($event)" contenteditable="true">
                            {{getStudent.ov}}
                        </td>
                        <td @blur="updateStudentName($event)" contenteditable="true">
                            {{getStudent.name}}
                        </td>
                        <td @blur="updateStudentYear($event)" contenteditable="true">
                            {{getStudent.year}}
                        </td>
                        <td>
                            <i @click="getStudent = null" class="fas fa-arrow-left"></i>
                            <i @click="editStudentById()" class="fas fa-save"></i>
                        </td>
                    </tr>
                </table>
                <table class="table table-responsive table-bordered table-striped">
                    <h4>Planned Exams for {{getStudent.name}}
                        <i class="fas fa-calendar-plus"></i>
                    </h4>
                    <label for="examName">Exam Name</label>
                    <select id="examName" name="" >
                        <option v-for="(exam, index) in getNotPlannedExams" :value="exam.id">{{exam.name}}</option>
                    </select>
                    <label for="date">Select Date</label>
                    <input onkeydown="return false" id="date" required="required" class="dateInput" type="date" name="date" min='1899-01-01' value="">
                    <input type="submit" name="" value="Submit" @click="createPlannedExam()">
                    <tr>
                        <th>Exam Name</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    <div v-for="(planned, index) in studentPlanned" >
                        <tr>
                            <td>{{planned.examName}}</td>
                            <td>{{planned.date}}</td>
                            <td>
                                <i @click="toggleEdit(index)"class="fa fas fa-pen"></i>
                                <i @click="deletePlannedExamById(planned)" class="fa fas fa-trash"></i>
                            </td>
                        </tr>
                        <div v-if="planned.showEdit">
                            <tr>
                                <th>Exam Name</th>
                                <th>Date</th>
                                <th>Save</th>
                            </tr>
                            <tr>
                                <td>
                                    <select id="newExam" v-model="selected.optionIndex">
                                        <option :value="index" v-for="(exam, index) in getNotPlannedExams2" >{{exam.name}}</option>
                                    </select>
                                </td>
                                <td>
                                    <label for="newDate">Select Date</label>
                                    <input onkeydown="return false" id="newDate" required="required" class="dateInput" type="date" name="newDate" v-model="selected.date">
                                </td>
                                <td><i @click="savePlannedExamById(studentPlanned, index)" class="fa fas fa-save"></i></td>
                            </tr>
                        </div>
                    </div>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'examcreator',
        data() {
            return {
                invitationInfo: {
                    email:"",
                    role:null,
                },
                today: "",
                searchExam: '',
                searchStudent: '',
                exams: [],
                error: null,
                parse_header: [],
                parse_csv: [],
                sortOrders:{},
                sortKey: '',
                csv: null,
                exam_states: [],
                studentsList: [],
                getStudent: null,
                studentPlanned: [],
                educations: [],
                editEducationToggle: false,
                editEducationData: null,
                selectedEducationId: -1,
                selected: {
                    date: "",
                    optionIndex: 0
                },
                invitations: [],
                confirmedExams: [],
                oldTemp: [],
                selectedItem: null,
            };
        },

        filters: {
            capitalize: function (str) {
                return str.charAt(0).toUpperCase() + str.slice(1)
            }
        },

        computed: {
            getStudentList() {
                if(this.studentsList.length == 0) { return []; }
                const searchStudent = this.searchStudent.toLowerCase();
                return this.studentsList.filter(function(student){
                    return student.name.toLowerCase().indexOf(searchStudent) > -1 ||
                    student.ov.toLowerCase().indexOf(searchStudent) > -1 ||
                    student.year.toLowerCase().indexOf(searchStudent) > -1
                });
            },
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
            },
            getExamList() {
                if(this.exams.length == 0) {return [];}
                const value = this.searchExam.toLowerCase();
                return this.exams.filter(function(exam){
                    return exam.name.toLowerCase().indexOf(value) > -1 || exam.year.toLowerCase().indexOf(value) > -1
                });
            },
            getNotPlannedExams() {
                var self = this;
                return this.exams.filter(function(plannedExam){
                    if (!self.studentPlannedContainsExamId(plannedExam.id)) {
                        return plannedExam.exam_states_id == 4;
                    }
                });
            },
            getNotPlannedExams2() {
                var self = this;
                return this.exams.filter(function(plannedExam){
                    if (!self.studentPlannedContainsExamId(plannedExam.id)) {
                        return plannedExam.exam_states_id == 4;
                    }
                });
            },

        },

        methods: {
            studentPlannedContainsExamId(id){
                for (var i = 0; i < this.studentPlanned.length; i++) {
                    if (this.studentPlanned[i].exam_id === id) {
                        return true;
                    }
                }
                return false;
            },
            disablePastDatesOfNewDate(){
                if (this.today === "") {
                    this.getToday();
                }
                document.getElementById("newDate").setAttribute("min", this.today);
            },
            setDateToToday() {
                if (this.today === "") {
                    this.getToday();
                }
                //Convert today to date input value
                Date.prototype.toDateInputValue = (function() {
                    var local = new Date(this);
                    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
                    return local.toJSON().slice(0,10);
                });
                document.getElementById("date").value = new Date().toDateInputValue();
                document.getElementById("date").setAttribute("min", this.today);
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
                this.today = today;
            },
            cancelInvitation(invitation) {
                if (confirm("Are you sure you want to cancel this Invitation?") === true) {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/deleteInvitationById',{id:invitation.id})
                        .then((response) => {
                            for (var i = 0; i < this.invitations.length; i++) {
                                if (this.invitations[i].id === invitation.id) {
                                    this.invitations.splice(i, 1);
                                }
                            }
                        })
                        .catch((err) =>{
                        alert('Invitation cannot be deleted')
                        })
                    });
                }
            },
            getInvitations(){
                new Promise((res, rej) => {
                    axios.get('/api/admin/getInvitations')
                    .then((response) => {
                        this.invitations = response.data;
                    })
                    .catch((err) => {
                        this.error = err.response.data.message;
                    })
                });
            },

            //send email with and create user
            inviteUser() {
                if (confirm("Are you sure you want to invite this person?") === true) {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/inviteUser', this.invitationInfo)
                        .then((response) => {
                            this.getInvitations();
                            console.log(response);
                        })
                        .catch((err) =>{
                            alert('Person cannot be invited')
                        })
                    });
                }
            },
            getConfirmedExams() {
                for (var i = 0; i < this.exams.length; i++) {
                    if (this.exams[i].exam_states_id === 4) {
                        this.confirmedExams.push(this.exams[i]);
                    }
                }
            },
            toggleEdit(index) {
                this.selected.date = this.studentPlanned[index].date;
                for (var i = 0; i < this.getNotPlannedExams2.length; i++) {
                        if (this.getNotPlannedExams2[i].name == this.oldTemp.examName) {
                            this.getNotPlannedExams2.splice([i], 1);
                        }
                }

                var temp = this.studentPlanned[index];
                temp.name = this.studentPlanned[index].examName;
                this.oldTemp = temp;

                this.getNotPlannedExams2.unshift(temp)
                this.studentPlanned[index].showEdit = !this.studentPlanned[index].showEdit;

                for (var i = 0; i < this.studentPlanned.length; i++) {
                    if (i != index) {
                        this.$set(this.studentPlanned[i], 'showEdit', false)
                    }
                }
                this.selected.optionIndex = 0;

                this.$nextTick(() => {
                    this.disablePastDatesOfNewDate();
                });
            },
            getPlannedExamsOfStudent(id) {
                this.studentPlanned = [];
                var dataToUpload = {
                    student_id: id
                }
                new Promise((res, rej) => {
                    axios.post('/api/admin/getPlannedExamsOfStudent', dataToUpload)
                    .then((response) => {
                        var temp = response.data;
                        for (var i = 0; i < temp.length; i++) {
                            temp[i]["showEdit"] = false;
                        }
                        this.studentPlanned = temp;
                        this.setDateToToday();
                    })
                    .catch((err) => {
                        alert("Cannot get planned exams of student");
                    })
                })
            },
            savePlannedExamById(studentPlanned, index) {
                this.studentPlanned[index].date = document.getElementById("newDate").value;
                for (var i = 0; i < this.exams.length; i++) {
                    if (this.exams[i].name === document.getElementById("newExam").options[newExam.selectedIndex].text) {
                        this.studentPlanned[index].exam_id === this.exams[i].id;
                        var examId = this.exams[i].id;
                    }
                }
                var toSaveData = {
                    student_id: this.studentPlanned[index].student_id,
                    exam_id: examId,
                    date: this.studentPlanned[index].date,
                    id: this.studentPlanned[index].id
                }
                new Promise((res, rej) => {
                    axios.post('/api/admin/savePlannedExamById', toSaveData)
                    .then((response) => {
                        this.getPlannedExamsOfStudent(this.getStudent.id)
                    })
                    .catch((err) =>{
                        alert('Planned exam cannot be saved')
                    })
                });
                this.studentPlanned[index].examName = document.getElementById("newExam").options[newExam.selectedIndex].text;
                this.studentPlanned[index].showEdit = !this.studentPlanned[index].showEdit;

            },
            deletePlannedExamById(plannedExam) {
                if (confirm("Are you sure you want to delete this Planned Exam ?") === true) {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/deletePlannedExamById',{id:plannedExam.id})
                        .then((response) => {
                            for (var i = 0; i < this.studentPlanned.length; i++) {
                                if (this.studentPlanned[i].id === plannedExam.id) {
                                    this.studentPlanned.splice(i, 1);
                                }
                            }
                        })
                        .catch((err) =>{
                            console.log(err);
                            alert('Planned exam cannot be deleted')
                        })
                    });
                }
            },
            getFormattedDate(date) {
                var newDate = new Date(date)
                let year = newDate.getFullYear();
                let month = (1 + newDate.getMonth()).toString().padStart(2, '0');
                let day = newDate.getDate().toString().padStart(2, '0');
                return day + '-' + month + '-' + year;
            },
            createPlannedExam() {
                //close open plannedExam
                var plannedExam = {
                    "student_id" : this.getStudent.id,
                    "exam_id" : document.getElementById("examName").value,
                    "date" : document.getElementById("date").value
                }

                new Promise((res, rej) => {
                    axios.post('/api/admin/createPlannedExam', plannedExam)
                    .then((response) => {
                        this.getPlannedExamsOfStudent(this.getStudent.id)
                    })
                    .catch((err) => {
                        alert("Cannot create plannedExam");
                    })
                });
            },
            getResultsOfStudent(id) {
                this.studentResults = [];
                new Promise((res, rej) => {
                    axios.get('/api/admin/getResultsOfStudent/' + id)
                        .then((response) => {
                            this.studentResults = response.data;
                        })
                        .catch((err) => {
                            alert("Cannot get student result(s)");
                        })
                });
            },
            updateStudentOv(elem) {
                this.getStudent.ov = elem.target.innerText;
            },
            updateStudentName(elem) {
                this.getStudent.name = elem.target.innerText;
            },
            updateStudentYear(elem) {
                this.getStudent.year = elem.target.innerText;
            },
            deleteStudentById(id) {
                this.getStudent = null;
                for (var i = 0; i < this.studentsList.length; i++) {
                    if (this.studentsList[i].id === id) {
                        //removes a space if there is one
                        var studentName = this.studentsList[i].name;
                        studentName = studentName.replace(/\s+/g,' ').trim();
                        if (confirm("Are you sure you want to delete " + studentName + "?") === true) {
                            this.studentsList.splice(i, 1);
                            new Promise((res, rej) => {
                                axios.post('/api/admin/deleteStudentById',{id:id})
                                .then((response) => {
                                })
                                .catch((err) => {
                                    alert('Exam cannot be deleted')
                                })
                            });
                        }
                    }
                }
            },
            getStudentInfo(data) {
                this.getStudent = data;
            },
            editStudentById() {
                new Promise((res, rej) => {
                    axios.post('/api/admin/editStudentById', this.getStudent)
                    .then((response) => {
                        this.getStudent = null;
                    })
                    .catch((error) => alert('Cannot get student info.'));
                });
            },
            copyExamById(id, originalName) {
                var self = this;
                var nameExtension = " - copy";
                var check = function(){
                    self.exams.forEach(function(exam){
                        if (exam.name == originalName + nameExtension) {
                            nameExtension += " - copy";
                            check();
                        }
                    })

                };
                check();
                new Promise((res, rej) => {
                    axios.post('/api/admin/copyExamById',{id:id, newName:originalName+nameExtension})
                    .then((response) => {
                        var newId = response.data;
                        self.$router.push({ name: 'exameditor', params: { id: newId}})
                    })
                    .catch((err) => {
                        alert('Exam cannot be copied')
                    })
                });
            },
            deleteExamById(id) {
                if (confirm("Are you sure") === true) {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/deleteExamById',{id:id})
                        .then((response) => {
                            for (var i = 0; i < this.exams.length; i++) {
                                if (this.exams[i].id === id) {
                                    this.exams.splice(i, 1);
                                }
                            }
                        })
                        .catch((err) =>{
                            alert('Exam cannot be deleted')
                        })
                    });
                }
            },
            getExams(){
                new Promise((res, rej) => {
                    axios.post('/api/admin/getExams', {id: this.currentUser.school_id})
                    .then((response) => {
                        this.exams = response.data;
                        this.$nextTick(() => {
                            this.getConfirmedExams();
                        });
                    })
                    .catch((err) =>{
                        this.error = err.response.data.message;
                    })
                });
            },
            getEducations(){
                new Promise((res, rej) => {
                    axios.post('/api/admin/getEducations', {id: this.currentUser['education_id']})
                    .then((response) => {
                        this.educations = response.data;
                    })
                    .catch((err) =>{
                        this.error = err.response.data.message;
                    })
                });
            },
            csvJSON(csv){
                var vm = this;
                var lines = csv.split("\n");
                var result = [];
                var headers = lines[0].split(",");
                vm.parse_header = lines[0].split(",");
                lines[0].split(",").forEach(function (key) {
                    vm.sortOrders[key] = 1;
                });
                lines.map(function(line, indexLine){
                    if (indexLine < 1) return; // Jump header line
                    var obj = {};
                    var currentline = line.split(",");
                    headers.map(function(header, indexHeader){
                        obj[header] = currentline[indexHeader];
                    });
                    result.push(obj);
                });
                result.pop(); // remove the last item because undefined values
                return result; // JavaScript object
            },
            loadCSV(e) {
                var vm = this;
                if (e.target.files.length == 0) {
                    alert("No file selected!");
                    this.parse_header = [];
                    this.parse_csv = [];
                    this.sortOrders ={};
                    this.sortKey = '';
                    this.csv = null;
                } else {
                    if (window.FileReader) {
                        var reader = new FileReader();
                        reader.readAsText(e.target.files[0]);
                        // Handle errors load
                        reader.onload = function(event) {
                            vm.csv = event.target.result;
                            vm.parse_csv = vm.csvJSON(vm.csv);
                        };
                        reader.onerror = function(evt) {
                            if(evt.target.error.name == "NotReadableError") {
                                alert("Cannot read file !");
                            }
                        };
                    } else {
                        alert('FileReader are not supported in this browser.');
                    }
                }
            },
            loadExamStates(){
                new Promise((res, rej) => {
                    axios.get('/api/admin/getexamstates')
                    .then((response) => {
                        this.exam_states = response.data;
                    })
                    .catch((err) => {
                        this.error = err.response.data.message;
                    })
                });
            },
            uploadConfirm() {
                var rows = this.csv.split('\r\n');
                var headers = rows[0].split(';');
                var result = [];
                for (var r = 1; r < rows.length - 1; r++) {
                    var rowData = rows[r].split(';');
                    var rowConverted = {};
                    for (var rd = 0; rd < headers.length; rd++) {
                        rowConverted[headers[rd]] = rowData[rd];
                    }
                    result.push(rowConverted);
                }
                var dataToUpload = [];
                //Database
                for (var i = 0; i < result.length; i++) {
                    var newStudent = result[i];
                    var found = false;
                    //Upload
                    for (var r = 0; r < this.studentsList.length; r++) {
                        var student = this.studentsList[r];
                        if (student.ov == newStudent.Nummer) {
                            found = true;
                        }
                    }
                    if (!found) {
                        newStudent['education_id'] = this.selectedEducationId;
                        newStudent['school_id'] = this.currentUser['school_id'];
                        dataToUpload.push(newStudent);
                    }
                }
                console.log(this.selectedEducationId);
                console.log(dataToUpload);
                if (dataToUpload.length > 0) {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/upload', {'data':JSON.stringify(dataToUpload)})
                        .then((response) => {
                            if (response.data == "invalid") {
                                alert("Please provide a valid file.");
                            } else {
                                alert("Upload successful!");
                                this.parse_header = [];
                                this.parse_csv = [];
                                this.csv = null;
                                document.getElementById("csv_file").value = null;
                            }
                        })
                        .catch((err) => {
                            alert("Please provide a valid file.");
                        })
                    });
                } else {
                    alert("No new students found!");
                }
                this.getStudents();
            },
            getStudents() {
                new Promise((res, rej) => {
                    axios.post('/api/admin/getStudents', {id: this.currentUser.school_id})
                    .then((response) => {
                        this.studentsList = response.data;
                    })
                    .catch((error) => {
                        alert('Cannot get Students.');
                    });
                });
            },
            editEducation(data){
                data = JSON.parse(JSON.stringify(data));
                if(this.editEducationData == null){
                    this.editEducationData = data;
                }

                if(this.editEducationData.id == data.id){
                    this.editEducationToggle = !this.editEducationToggle;
                }
                this.editEducationData = data;
            },
            confirmEditEducation(){
                new Promise((res, rej) => {
                    axios.post('/api/admin/editEducation',{data:this.editEducationData})
                    .then((response) => {
                        this.editEducationToggle = false;
                        for (var i = 0; i < this.educations.length; i++) {
                            if (this.educations[i].id === this.editEducationData.id) {
                                this.educations[i] = this.editEducationData;
                            }
                        }
                    })
                    .catch((err) =>{
                        console.log(err);
                        alert('Education cannot be updated');
                    })
                });
            },
            deleteEducationById(id){
                if (confirm("Are you sure") === true) {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/deleteEducationById',{id:id})
                        .then((response) => {
                            for (var i = 0; i < this.educations.length; i++) {
                                if (this.educations[i].id === id) {
                                    this.educations.splice(i, 1);
                                }
                            }
                        })
                        .catch((err) =>{
                            alert('Education cannot be deleted')
                        })
                    });
                }
            },
            newEducation(){
                new Promise((res, rej) => {
                    axios.post('/api/admin/newEducation', {id: this.currentUser.school_id})
                    .then((response) => {
                        this.educations.push(response.data);
                    })
                    .catch((err) =>{
                        alert('Education cannot be created');
                    })
                });
            }
        },
        mounted(){
            this.getInvitations();
            this.getExams();
            this.getEducations();
            this.loadExamStates();
            this.getStudents();
        },
    }
</script>

<style>
.fas, .fas:hover, .fas:active{
    color: black;
    text-decoration: none;
}
.pointer:hover{
    cursor: pointer;
}

#csv_file {
    padding-bottom: 4vh;
}

.tableBackground {
    border: 1px solid darkgray !important;
    border-radius: 8px;
    padding: 1%;
    background-color: #eaeaea;
    margin-top: 2vh !important;
}

.tableBackground:last-of-type {
    margin-bottom: 2vh;
}

.addUserTable {
    margin-top: 0 !important;
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
@media (max-width: 991px) {
    .tableBackground {

    }

}
</style>
