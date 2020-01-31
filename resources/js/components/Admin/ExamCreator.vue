<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row d-inline align-items-start align-self-start justify-content-around mt-2 card-container">
            <div class="card-large d-flex align-self-start flex-column d-inline justify-content-around">
                <draggable animation="500" :list="groups" handle=".handle" :tag="'div'">
                <div class="card mb-2 group" v-for="(group, groupIndex) in groups">
                    <div class="card-body">
                        <div class="handle">
                            
                        <h1 style="display: inline-block;">
                            #{{groupIndex + 1}}
                        </h1>
                        <input type="text" class="form-control" style="max-width: calc(100% - 75px); display: inline-block;" v-model="groups[groupIndex].name">
                        <div class="dropdown group-dropdown">
                            <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                <p class="dropdown-item pointer" data-toggle="collapse" :data-target="'#group' + groupIndex" aria-expanded="false" :aria-controls="'#group' + groupIndex">Toggle</p>
                                <p class="dropdown-item pointer" v-on:click="removeGroup(groupIndex)">Delete</p>

                                <p class="dropdown-item pointer" v-on:click="changeGPos(groupIndex, -1)" v-if="groupIndex > 0">Move up</p>
                                <p class="dropdown-item not-allowed" v-else>Move up</p>

                                <p class="dropdown-item pointer" v-on:click="changeGPos(groupIndex, 1)" v-if="groupIndex < groups.length - 1">Move down</p>
                                <p class="dropdown-item not-allowed" v-else>Move down</p>
                            </div>
                        </div>
                    </div>
                        <div class="table-responsive show" :id="'group' + groupIndex">
                            
                            <table class="table table-hover table-large">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">Required Mark</th>
                                        <th>Question:</th>
                                        <th style="width: 25px">Actions</th>
                                    </tr>
                                </thead>
                                <draggable animation="200" :list="groups[groupIndex].questions" handle=".my-handle" :tag="'tbody'">
                                    <tr v-for="(question, questionIndex) in group.questions" class="my-handle">
                                        <td>
                                            <select v-model="groups[groupIndex].questions[questionIndex].requiredMark" class="form-control custom-select">
                                                <option v-for="(value, cesuurIndex) in cesuur.marks" v-bind:value="cesuurIndex" v-bind:selected="cesuurIndex == question.requiredMark">{{value}}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="questionExplanation">
                                                <i v-on:click="toggleExplanation(groupIndex + '_' + questionIndex + '_explanation')" class="fas fa-info-circle" style="float:left;"></i>
                                                <textarea class="allowNewLine form-control" style="resize: none; width: calc(100% - 25px);" rows="4" v-model="groups[groupIndex].questions[questionIndex].question"></textarea>
                                            </div>
                                            <br />
                                            <textarea :id="groupIndex + '_' + questionIndex + '_explanation'" class="allowNewLine form-control hide" style="resize:none; width:calc(100% - 25px); margin-left: 15px;" rows="4" v-model="groups[groupIndex].questions[questionIndex].explanation"></textarea>
                                        </td>
                                        <td>
                                            <div class="dropdown my-handle">
                                                <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <p class="dropdown-item pointer" v-on:click="removeQuestion(groupIndex, questionIndex)">Delete</p>

                                                    <p class="dropdown-item pointer" v-on:click="changeQPosU(groupIndex, questionIndex, -1)" v-if="questionIndex > 0">Move up</p>
                                                    <p class="dropdown-item not-allowed" v-else>Move up</p>

                                                    <p class="dropdown-item pointer" v-on:click="changeQPosD(groupIndex, questionIndex, 1)" v-if="questionIndex < group.questions.length - 1">Move down</p>
                                                    <p class="dropdown-item not-allowed" v-else>Move down</p>

                                                    <p class="dropdown-item pointer" v-on:click="changeQPosUG(groupIndex, questionIndex, -1)" v-if="groupIndex > 0">Move up (group)</p>
                                                    <p class="dropdown-item not-allowed" v-else>Move up (group)</p>

                                                    <p class="dropdown-item pointer" v-on:click="changeQPosDG(groupIndex, questionIndex, 1)" v-if="groupIndex != groups.length-1">Move down (group)</p>
                                                    <p class="dropdown-item not-allowed" v-else>Move down (group)</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                            </draggable>
                            </table>
                            <div class="text-center" style="width: 100%;" v-on:click="addQuestion(groupIndex)">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
                </draggable>
                <div class="card" v-on:click="addGroup()">
                    <div class="card-body text-center">
                        <i class="fas fa-plus"></i>
                    </div>
                </div>
            </div>
            <div class="card card-small">
                <div class="card-body">
                    <h5 class="card-title mt-2">
                        <b>Exam Details:</b>
                    </h5>
                    <p class="card-text">
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" class="form-control" v-model="data.name">
                        </div>
                        <div class="form-group">
                            <label>Year:</label>
                            <input type="text" class="form-control" v-model="data.year">
                        </div>
                        <div class="form-group" v-if="currentUser.role_id == getRoleByString('superAdmin')">
                            <label>Education:</label>
                            <select class="form-control custom-select" v-model="data.education">
                                <option v-for="education in educations" :value="education.id">{{education.name}}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status:</label>
                            <select class="form-control custom-select" v-model="data.exam_state">
                                <option v-for="(exam_state) in exam_states" :value="exam_state.id">{{exam_state.name}}</option>
                            </select>
                        </div>
                        <label>Marks:</label>
                        <div class="table-responsive">
                            <table class="table table-hover table-small">
                                <tr v-for="(mark, markIndex) in cesuur.marks">
                                    <td>
                                        <input type="text" class="form-control" v-model="cesuur.marks[markIndex]">
                                    </td>
                                    <td>
                                        <i class="fas fa-trash pointer" v-on:click="removeMark(markIndex)"></i>
                                    </td>
                                </tr>
                            </table>
                            <div class="text-center" style="width: 100%;" v-on:click="addMark()">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                        <br />
                        <label>Cesuur:</label>
                        <div class="table-responsive mb-5">
                            <table class="table table-hover table-small">
                                <tr v-for="(calculation, calculationIndex) in cesuur.calculations">
                                    <td style="width: 75px">
                                        <input style="width: 75px" class="form-control" v-model="cesuur.calculations[calculationIndex].name">
                                    </td>
                                    <td>
                                        <textarea style="resize:none;width:100%;" class="form-control" v-model="cesuur.calculations[calculationIndex].calculation" rows="4"></textarea>
                                    </td>
                                    <td style="width: 20px">
                                        <i class="fas fa-trash pointer" v-on:click="removeCalculation(calculationIndex)"></i>
                                    </td>
                                </tr>
                            </table>
                            <div class="text-center" style="width: 100%;" v-on:click="addCalculation()">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" v-on:click="checkCesuur()">Test Cesuur</button>
                        <button type="button" class="btn btn-outline-primary" style="float:right;" v-on:click="saveExam()">Save Exam</button>
                    </p>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import { getRoleByString } from "../../helpers/roles";
    import draggable from 'vuedraggable';
    export default {
        components: {
            draggable
        },
        name: 'examcreator',
        props: ['id'],
        data() {
            return {
                cesuur: {
                    'marks' : [],
                    'calculations' : []
                },
                groups: [],
                exam_states: [],
                educations: [],
                prevExplanationId: null,
                data: {
                    name: 'Exam Name Here',
                    year: 2019,
                    exam_state: '1',
                    education: '1'
                },
                error: null
            };
        },
        computed: {
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                }
            },
        },
        methods: {
            back(){
                this.$router.push({name: 'AdminExams'});
            },
            getRoleByString(name){
                return getRoleByString(name);
            },
            //Changes Group postion
            changeGPos(old_index, new_index) {
                this.groups.splice(new_index+=old_index, 0, this.groups.splice(old_index, 1)[0]);
            },
            //Changes Question Postion Up
            changeQPosU(groupIndex, question_index, new_index) {
                this.groups[groupIndex].questions.splice(question_index+=new_index, 0, this.groups[groupIndex].questions.splice(question_index+1, 1)[0]);
            },
            //Changes Question Postion Down
            changeQPosD(groupIndex, question_index, new_index) {
                this.groups[groupIndex].questions.splice(question_index+=new_index, 0, this.groups[groupIndex].questions.splice(question_index-1, 1)[0]);
            },
            //Changes Question Postion Up Group
            changeQPosUG(groupIndex, question_index, new_index) {
                this.groups[groupIndex+=new_index].questions.splice(this.groups[groupIndex].questions.length, 0, this.groups[groupIndex+1].questions.splice(question_index, 1)[0]);
            },
            //Changes Question Postion Down Group
            changeQPosDG(groupIndex, question_index, new_index) {
                this.groups[groupIndex+=new_index].questions.splice(0, 0, this.groups[groupIndex-1].questions.splice(question_index, 1)[0]);
            },
            checkCesuur(){
                var self = this;
                var marks = [];
                //create list of marks with amount and max amount
                for (var i = 0; i < this.cesuur.marks.length; i++) {
                    marks.push({
                        'name': this.cesuur.marks[i],
                        'amount': 0,
                        'maxAmount': this.getMaxAmount(i)
                    });
                }

                var check = function () {
                    //marks hasnt reached max amount check
                    if(!eval(self.getMarkCheckString(marks))){
                        //loop through all calculations
                        var hasFound = false;
                        var hasOne = true;
                        for (var i = 0; i < self.cesuur.calculations.length; i++) {
                            var stringData = JSON.parse(JSON.stringify(self.cesuur.calculations[i])).calculation;
                            for (var r = 0; r < marks.length; r++) {
                                stringData = stringData.split('[' + marks[r].name + ']').join(marks[r].amount);
                            }

                            if(eval(stringData)){
                                //found result
                                if(hasFound){
                                    hasOne = false;
                                }

                                hasFound = true;
                            }
                        }

                        //check if code was able to find correct calculation
                        if(hasFound && hasOne){
                            for (var i = 0; i < marks.length; i++) {
                                if(i != marks.length-1){
                                    if(marks[i].amount == marks[i].maxAmount && marks[i+1].amount < marks[i+1].maxAmount){
                                        marks[i].amount = 0;
                                        marks[i + 1].amount++;
                                    }else if(i == 0){
                                        marks[i].amount++;
                                    }
                                }
                            }

                            var allComplete = true;
                            for (var i = 0; i < marks.length; i++) {
                                if(marks[i].amount != marks[i].maxAmount){
                                    allComplete = false;
                                }
                            }

                            if(allComplete){
                                self.$noty.success("No errors found in the current calculations");
                                return;
                            }else{
                                check();
                            }
                        } else {
                            //did not find a correct calculation
                            var markString = "";
                            for (var i = 0; i < marks.length; i++) {
                                if(i != 0) {
                                    markString += ", ";
                                }
                                markString += marks[i].name + " = " + marks[i].amount;
                            }
                            self.$noty.warning("Error: Could not find a correct a mark for '" + markString + "'");
                        }
                    }
                };
                check();
            },
            getMarkCheckString(marks){
                var checkString = "";
                for (var i = 0; i < marks.length; i++) {
                    if(i != 0) {
                        checkString += "&&";
                    }
                    checkString += marks[i].amount + ">=" + marks[i].maxAmount;
                }
                return checkString;
            },
            getMaxAmount(index){
                var count = 0;
                for (var i = 0; i < this.groups.length; i++) {
                    var group = this.groups[i];
                    for (var r = 0; r < group.questions.length; r++) {
                        var question = group.questions[r];
                        if(group.questions[r].requiredMark == index){
                            count++;
                        }
                    }
                }
                return count;
            },
            loadExam(){
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/admin/getExamById', {id:this.id})
                    .then((response) => {
                        if (this.id != response.data.id) {
                            this.$noty.error("This exam does not exists");
                            self.$router.push({name:'admin'});
                        }
                        if (response.data.exam_states_id === 4) {
                            this.$noty.error("This exam cannot be edited");
                            self.$router.push({ name: 'admin'});
                        }

                        var template = JSON.parse(response.data.template);
                        this.groups = template['groups'];
                        this.data.name = response.data.name;
                        this.data.year = response.data.year;
                        this.data.exam_state = response.data.exam_states_id;
                        this.data.education = response.data.education_id;

                        this.cesuur = template['cesuur'];
                        if(this.cesuur.length <= 0){
                            this.cesuur = {
                                'marks' : [],
                                'calculations' : []
                            };
                        }
                    })
                    .catch((err) =>{
                        this.$noty.error("This exam does not exists");
                    })
                });
            },
            loadExamStates(){
                new Promise((res, rej) => {
                    axios.get('/api/admin/getexamstates')
                    .then((response) => {
                        this.exam_states = response.data;
                    })
                    .catch((err) =>{
                        this.$noty.error("Could not load examStates");
                    })
                });
            },
            loadEducations(){
                new Promise((res, rej) => {
                    axios.post('/api/admin/geteducations', {id: this.currentUser.domain_id})
                    .then((response) => {
                        this.educations = response.data;
                    })
                    .catch((err) =>{
                        this.$noty.error("Could not load educations");
                    })
                });
            },
            addGroup(){
                var group = {
                    name: 'Group Title',
                    questions: []
                };
                this.groups.push(group);
            },
            addQuestion(index){
                var question = {
                    question: 'question here',
                    explanation: 'explanation here',
                    requiredMark: 0
                };
                this.groups[index].questions.push(question);
            },
            addCalculation(){
                var calculation = {
                    name: 'onvoldoende',
                    calculation: '[onv] < 2 && [vold] + [goed] - [onv] >= 9',
                };
                this.cesuur.calculations.push(calculation);
            },
            addMark(){
                this.cesuur.marks.push('onv');
            },
            removeQuestion(groupIndex, questionIndex){
                this.groups[groupIndex].questions.splice(questionIndex, 1);
            },
            removeGroup(groupIndex){
                this.groups.splice(groupIndex, 1);
            },
            removeCalculation(cesuurIndex){
                this.cesuur.calculations.splice(cesuurIndex, 1);
            },
            removeMark(cesuurIndex){
                this.cesuur.marks.splice(cesuurIndex, 1);
            },
            toggleExplanation(id){
                document.getElementById(id).classList.toggle('hide');
                document.getElementById(id).focus();
                if(this.prevExplanationId != null && this.prevExplanationId != id){
                    document.getElementById(this.prevExplanationId).classList.add('hide');
                }
                this.prevExplanationId = id;
            },
            saveExam(){
                var self = this;
                if (this.data.exam_state === 4) {
                    this.$dialog
                    .confirm({
                        title: "Warning",
                        body: "Are you sure you want to change the exam state to confirmed? (Cannot be undone)",
                    })
                    .then(dialog => {
                        save();
                    })
                    .catch(() => {
                       return false;
                    });
                    return;
                }else{
                    save();
                }

                function save(){
                    var toSaveData = {
                        name: self.data.name,
                        year: self.data.year,
                        exam_state: self.data.exam_state,
                        education_id: self.data.education,
                        domain_id: self.currentUser.domain_id,
                        template: JSON.stringify({"groups": self.groups, "cesuur": self.cesuur})
                    };

                    if(self.currentUser.role_id != getRoleByString("superAdmin")){
                        toSaveData.education_id = self.currentUser.education_id;
                    }

                    if(self.id >= 0){
                        toSaveData.id = self.id;
                    }

                    new Promise((res, rej) => {
                        axios.post('/api/admin/save', toSaveData)
                        .then((response) => {
                            self.$noty.success("Successfully saved exam");
                            self.$router.push('/adminexams');
                        })
                        .catch((err) =>{
                            self.$noty.error("Cannot be saved");
                        })
                    });
                }
            }
        },
        mounted(){
            if(this.id >= 0){
                this.loadExam();
            }
            this.loadExamStates();
            this.loadEducations();
        }
    }
</script>

<style scoped>
.container{
    max-width: 90%;
}

.card-container{
    flex-wrap: wrap;
}

.card-large{
    width: 70%;
}

.card-small{
    width: 29%;
    margin-left: 1%;
}

.dropdown-item{
    margin-bottom: 0px;
}

.text-center{
    text-align: center;
}

.not-allowed:hover, .not-allowed{
    cursor: not-allowed !important;
    color: gray;
}

.card-body{
    position: relative;
}

.group-dropdown{
    position: absolute;
    right: 20px;
    top: 30px;
}

@media (max-width: 426px) {
    .card{
        width: 100%;
    }

    .card-large{
        width: 100%;
    }

    .card-small{
        margin-left: 0px;
    }

    .card-container{
        flex-wrap: wrap-reverse;
    }
}

</style>
