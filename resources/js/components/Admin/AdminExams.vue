<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Exams</h5>
                    <div class="input-group sticky-top mt-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" style="border: none;" v-model="searchExam" placeholder="Search Exam">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th>Name</th>
                                    <th>Year</th>
                                    <th>State</th>
                                    <th>Education</th>
                                    <th>Domain</th>
                                    <th>
                                        Actions
                                        <i class="fas pointer fa-plus ml-2" v-on:click="redirect('ExamCreator', null)"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(exam, index) in getExamList">
                                    <th>{{index+1}}</th>
                                    <td>{{exam.name}}</td>
                                    <td>{{exam.year}}</td>
                                    <td>
                                        <div v-for="state in exam_states">
                                            <p v-if="state.id == exam.exam_states_id">
                                                {{state.name}}
                                            </p>
                                        </div>
                                    </td>
                                    <td>{{exam.education.name}}</td>
                                    <td>{{exam.domain.name}}</td>
                                    <td>
                                        <i class="fas pointer fa-pen mr-2" v-on:click="redirect('ExamEditor', exam.id)" v-if="exam.exam_states_id != 4"></i>
                                        <i class="fas fa-pen mr-2" v-else style="color:grey;"></i>
                                        <i class="fas pointer fa-copy mr-2" @click="copyExamById(exam.id, exam.name)"></i>
                                        <i class="fas pointer fa-trash" @click="deleteExamById(exam.id)"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                searchExam: '',
                exams: [],
                error: null,
                exam_states: [],
                confirmedExams: [],
            };
        },

        computed: {
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
                        self.$router.push({ name: 'ExamEditor', params: { 'id': newId }});
                        self.$noty.success("Exam copied");
                    })
                    .catch((err) => {
                        self.$noty.error(err.response.data.message);
                        self.createLog(self.currentUser, `Tried to copy exam of ${id} but failed`);
                    })
                });
            },
            deleteExamById(id) {
                var self = this;
                this.$dialog
                .confirm({
                    title: "Warning",
                    body: "Are you sure you want to delete this exam?",
                })
                .then(dialog => {
                    new Promise((res, rej) => {
                        axios.post('/api/admin/deleteExamById',{id:id})
                        .then((response) => {
                            for (var i = 0; i < this.exams.length; i++) {
                                if (this.exams[i].id === id) {
                                    this.exams.splice(i, 1);
                                }
                            }
                            self.$noty.success("Exam deleted");
                        })
                        .catch((err) =>{
                            self.createLog(self.currentUser, `Tried to delete exam but failed`);
                            self.$noty.error("Exam cannot be deleted");
                        })
                    });
                })
                .catch((err) => {});
            },
            getExams(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/admin/getExams', {education_id: this.currentUser.education_id, domain_id: this.currentUser.domain_id})
                    .then((response) => {
                        self.exams = response.data;
                        self.$nextTick(() => {
                            //this.getConfirmedExams();
                        });
                    })
                    .catch((err) =>{
                        self.createLog(self.currentUser, `Tried to load exams but recieved an error`);
                        self.$noty.error("Cannot get exams");
                    })
                });
            },
            loadExamStates(){
                var self = this;
                new Promise((res, rej) => {
                    axios.get('/api/admin/getexamstates')
                    .then((response) => {
                        self.exam_states = response.data;
                    })
                    .catch((err) => {
                        self.createLog(self.currentUser, `Tried to load exam states but recieved an error`);
                        self.$noty.error("Cannot get exam states");
                    })
                });
            }
        },
        mounted(){
            this.getExams();
            this.loadExamStates();
        },
    }
</script>

<style scoped>
table { table-layout: fixed; }
table th, table td { overflow: hidden; }

td span{
    color: rgba(0,0,0,0.65);
    font-size: 0.85em;
}

.card{
    width: 100%;
}
</style>
