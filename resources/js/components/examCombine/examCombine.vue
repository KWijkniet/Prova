<template>
    <div class="row page">
        <div class="col-md-9 ExamEditorContainer">
            <div class="leftSide">
                <div v-for="(group, groupIndex) in groupsFiltered" class="groups tableBackground">
                    <p class="question-title">{{group.name}}:</p>
                    <p class="header-info pd">Percieved, Doubt</p>
                    <div v-for="(question, questionIndex) in group.questions">
                        <div class="question row">
                            <p class="col question-required-mark allowNewLine">({{cesuurs.marks[question.requiredMark]}})</p>
                            <p class="col allowNewLine" v-on:click="question.show = !question.show">
                                <i v-on:click="toggleQuestion(groupIndex + '-' + questionIndex + '_question')" class="fas fa-info-circle"></i>
                                {{question.question}}
                            </p>
                            <div class="col question-answer">
                                <input type="checkbox" :checked="checkAnswer(groupIndex, questionIndex)" disabled>
                                <input type="checkbox" :checked="checkDoubt(groupIndex, questionIndex)" disabled>

                                <i class="fas" v-bind:class="question.icon" v-on:click="toggleDifference(groupIndex + '-' + questionIndex + '_difference')"></i>
                            </div>
                        </div>
                        <p :id="groupIndex + '-' + questionIndex + '_question'" class="explanation hide col-sm-12 allowNewLine">{{question.explanation}}</p>
                        <div class="hide row" :id="groupIndex + '-' + questionIndex + '_difference'">
                            <div class="col-md-3">
                                <p class="col">Examinator:</p>
                                <div class="col" v-for="(value, key) in instances[0].answers.groups[groupIndex].answers[questionIndex]">
                                    <span>{{key}}:</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-for="difference in instances">
                                <p class="col">{{difference.examiner.username}}</p>
                                <div class="col" v-for="(value, key) in difference.answers.groups[groupIndex].answers[questionIndex]">
                                    <input type="checkbox" v-if="key != 'comment'" :checked="value" disabled>
                                    <div class="allowNewLine" style="max-height: 200px; overflow-y:auto;" v-if="key == 'comment'">{{value}}</div>
                                </div>
                            </div>
                            <div class="col-md-3 question-answer">
                                <p class="col">Percieved</p>

                                Ja<input type="radio" :name="'confirm-' + groupIndex + '-' + questionIndex" value="1" v-on:click="updateConfirm(groupIndex, questionIndex, $event.target)" :checked="checkAnswer(groupIndex, questionIndex) == true"  :disabled="isLocked(groupIndex, questionIndex)">
                                Nee<input type="radio" :name="'confirm-' + groupIndex + '-' + questionIndex" value="0" v-on:click="updateConfirm(groupIndex, questionIndex, $event.target)" :checked="checkAnswer(groupIndex, questionIndex) == false"  :disabled="isLocked(groupIndex, questionIndex)">

                            </div>
                            <textarea :id="groupIndex + '-' + questionIndex + '_comment'" class="col-sm-11" style="max-height: 250px;overflow-y: auto; margin:20px;" v-on:keydown.enter.prevent="allowNewLine(groupIndex + '-' + questionIndex + '_comment')" @blur="addComment(groupIndex, questionIndex, $event.target)" oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="rightSide tableBackground">
                <table>
                    <tr>
                        <td>Filters</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter" v-on:click="filter(0)" checked="true"></td>
                        <td>None</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter" v-on:click="filter(1)"></td>
                        <td>No Conflict</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter" v-on:click="filter(2)"></td>
                        <td>Conflicts</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter" v-on:click="filter(3)"></td>
                        <td>Doubts</td>
                    </tr>
                </table>
                <button class="submitExam" type="button" name="button" v-on:click="confirmExam()">Submit</button>
            </div>
        </div>

        <div class="col-md-9 rightSideResponsive">
            <div class="tableBackground">
                <table>
                    <tr>
                        <td>Filters</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter1" v-on:click="filter(0)" checked="true"></td>
                        <td>None</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter1" v-on:click="filter(1)"></td>
                        <td>No Conflict</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter1" v-on:click="filter(2)"></td>
                        <td>Conflicts</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="filter1" v-on:click="filter(3)"></td>
                        <td>Doubts</td>
                    </tr>
                </table>
                <button class="submitExam" type="button" name="button" v-on:click="confirmExam()">Submit</button>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: "examSheet",
    props: ['instanceId'],
    data() {
        return {
            error: null,
            groups:  {},
            groupsFiltered:  {},
            cesuurs: {},
            instances: null,
            results: null,
            prevQuestionId: null,
            prevCommentId: null,
            conflictCount: 0
       };
    },
    computed: {
        currentUser: {
            get: function () {
                return this.$store.state.currentUser;
            },
            set: function () {
            }
        }
    },
    methods: {
        getExam(){
            var self = this;
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.instances[0].exam_id)
                .then((response) => {
                    this.groups = JSON.parse(response.data.template)['groups'];
                    this.groupsFiltered = JSON.parse(response.data.template)['groups'];
                    this.cesuurs = JSON.parse(response.data.template)['cesuur'];

                    for(var i = 0; i < this.groups.length; i++){
                        var group = this.groups[i];
                        for(var r = 0; r < group.questions.length; r++){
                            var question = group.questions[r];
                            self.$set(self.groups[i].questions[r], 'icon', '');
                            question['show'] = false;

                            if(this.results.groups[i].answers[r]['doubt'] == 1){
                                question['icon'] = "fa-exclamation";
                                question['conflict'] = "doubt";
                            }else if(this.results.groups[i].answers[r]['percieved'] == -1){
                                question['icon'] = "fa-exclamation-triangle";
                                question['conflict'] = "conflict";
                            }else{
                                question['icon'] = "fa-info-circle";
                                question['conflict'] = "none";
                            }
                        }
                    }
                    this.filter(0);
                })
                .catch((err) =>{
                    //alert('Exam cannot be pulled.')
                })
            });
        },
        getInstances() {
            new Promise((res, rej) => {
                axios.get('/api/examcombine/getinstances/' + this.instanceId)
                .then((response) => {
                    this.instances = response.data;
                    for (var i = 0; i < this.instances.length; i++) {
                        var lists = this.instances[i];
                        lists.answers = JSON.parse(lists.answers);
                    }
                    this.results = JSON.parse(JSON.stringify(this.instances[0].answers));
                    for (var i = 0; i < this.results['groups'].length; i++) {
                        var group = this.results['groups'][i];
                        for (var r = 0; r < group.answers.length; r++) {
                            var answer = group.answers[r];
                            answer.percieved = this.instances[0].answers.groups[i].answers[r].percieved == this.instances[1].answers.groups[i].answers[r].percieved ? this.instances[1].answers.groups[i].answers[r].percieved : -1;

                            answer.doubt = this.instances[0].answers.groups[i].answers[r].doubt == 1 || this.instances[1].answers.groups[i].answers[r].doubt == 1 ? 1 : 0;
                            answer.comment = "";

                            if(this.instances[0].answers.groups[i].answers[r].percieved != this.instances[1].answers.groups[i].answers[r].percieved){
                                this.conflictCount++;
                            }
                        }
                    }
                    this.getExam();
                })
                .catch((err) =>{
                    alert('Instances cannot be pulled.')
                })
            });
        },
        checkDoubt(groupIndex, answerIndex){
            var returnData = false;
            for (var i = 0; i < this.instances.length; i++) {
                var instance = this.instances[i];
                var current = instance.answers.groups[groupIndex].answers[answerIndex]['doubt'];
                if(this.instances[i+1] != undefined){
                    var next = this.instances[i+1].answers.groups[groupIndex].answers[answerIndex]['doubt'];
                    if(current == next && current == 1){
                        returnData = true;
                    }else{
                        returnData = false;
                    }
                }
            }
            return returnData;
        },
        checkAnswer(groupIndex, answerIndex){
            var returnData = false;
            for (var i = 0; i < this.instances.length; i++) {
                var instance = this.instances[i];
                var current = instance.answers.groups[groupIndex].answers[answerIndex]['percieved'];
                if(this.instances[i+1] != undefined){
                    var next = this.instances[i+1].answers.groups[groupIndex].answers[answerIndex]['percieved'];
                    if(current == next && current == 1){
                        returnData = true;
                    }else if(current == next && current == 0){
                        returnData = false;
                    }else{
                        return null;
                    }
                }
            }
            return returnData;
        },
        allowNewLine(id) {
            var elem = document.getElementById(id);
            elem.value = elem.value + "\n";
            return false;
        },
        updateConfirm(groupIndex, answerIndex, elem){
            this.conflictCount--;

            //change icon from ...  to no conflicts
            this.groups[groupIndex].questions[answerIndex]['icon'] = "fa-info-circle";

            if(elem.value == 1){
                this.results['groups'][groupIndex].answers[answerIndex]['percieved'] = 1;
            }else{
                this.results['groups'][groupIndex].answers[answerIndex]['percieved'] = 0;
            }
        },
        addComment(groupIndex, answerIndex, elem){
            this.results['groups'][groupIndex].answers[answerIndex].comment = elem.value.length > 0 ? elem.value : "";
        },
        toggleQuestion(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevQuestionId != null && this.prevQuestionId != id){
                document.getElementById(this.prevQuestionId).classList.add('hide');
            }
            this.prevQuestionId = id;
        },
        toggleDifference(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevCommentId != null && this.prevCommentId != id){
                document.getElementById(this.prevCommentId).classList.add('hide');
            }
            this.prevCommentId = id;
        },
        filter(type){
            console.log(type);
            //this.groupsFiltered = visible list
            //this.groups = original list
            var copy = JSON.parse(JSON.stringify(this.groups));
            var result = [];

            for (var i = 0; i < copy.length; i++) {
                var group = copy[i];
                for (var r = group.questions.length-1; r >= 0; r--) {
                    var answer = group.questions[r];
                    if(answer['conflict'] != "conflict" && type == 2){
                        group.questions.splice(r, 1);
                    }
                    else if(answer['conflict'] != "doubt" && type == 3){
                        group.questions.splice(r, 1);
                    }
                    else if(answer['conflict'] != "none" && type == 1){
                        group.questions.splice(r, 1);
                    }
                }
            }

            this.groupsFiltered = copy;
        },
        confirmExam(){
            var self = this;
            var submit = false;

            if(this.conflictCount > 0){
                if(confirm("You have " + this.conflictCount + " possible unsolved conflicts. Are you sure you want to submit this exam?\nyou CAN'T undo this action")){
                    submit = true;
                }
            }else{
                submit = true;
            }

            if (submit) {
                var mark = this.calculateMark();
                //create row in results table.
                var data = {
                    "instances_id": [],
                    "results": this.results
                };
                for (var i = 0; i < this.instances.length; i++) {
                    data.instances_id.push(this.instances[i].id);
                }

                var dataToUpload = {
                    "exam_id": this.instances[0].exam_id,
                    "student_id": this.instances[0].student_id,
                    "mark": mark,
                    "data": JSON.stringify(data),
                }

                new Promise((res, rej) => {
                    axios.post('/api/examcombine/createresult', {data: dataToUpload})
                    .then((response) => {
                        alert(dataToUpload['mark']);
                        self.$router.push({ path: '/' });
                    })
                    .catch((err) =>{})
                });
            }
        },
        calculateMark(){
            //preset points to 0
            var calcData = {};
            for (var i = 0; i < this.cesuurs.marks.length; i++) {
                calcData[this.cesuurs.marks[i]] = 0;
            }

            //calculate points
            for (var i = 0; i < this.results.groups.length; i++) {
                var group = this.results.groups[i];
                for (var r = 0; r < group.answers.length; r++) {
                    var answer = group.answers[r];
                    if(answer.percieved){
                        calcData[this.cesuurs.marks[this.groups[i].questions[r].requiredMark]]++;
                    }
                }
            }

            //calculate statement
            for (var i = 0; i < this.cesuurs.calculations.length; i++) {
                for (var r = 0; r < this.cesuurs.marks.length; r++) {
                    var string = '[' + this.cesuurs.marks[r] + ']';
                    this.cesuurs.calculations[i].calculation = this.cesuurs.calculations[i].calculation.split(string).join(calcData[this.cesuurs.marks[r]]);
                }
            }

            //calculate mark
            for (var i = 0; i < this.cesuurs.calculations.length; i++) {
                if(eval(this.cesuurs.calculations[i].calculation)){
                    return this.cesuurs.calculations[i].name;
                }
            }
            return "Cesuur has a calculation error";
        },
        isLocked(groupIndex, questionIndex){
            var returnData = false;
            for (var i = 0; i < this.instances.length; i++) {
                var instance = this.instances[i];
                var current = instance.answers.groups[groupIndex].answers[questionIndex]['percieved'];
                var currentDoubt = instance.answers.groups[groupIndex].answers[questionIndex]['doubt'];
                if(this.instances[i+1] != undefined){
                    var next = this.instances[i+1].answers.groups[groupIndex].answers[questionIndex]['percieved'];
                    var nextDoubt = this.instances[i+1].answers.groups[groupIndex].answers[questionIndex]['doubt'];
                    if((current == next && current == 1) || (current == next && current == 0) && currentDoubt == 0 && nextDoubt == 0){
                        returnData = true;
                    }else{
                        returnData = false;
                    }
                }
            }
            return returnData;
        }
    },
    mounted(){
        this.getInstances();
    },
}
</script>

<style scoped>
    .page{
        width: 90%;
        margin-left: 5%;
    }

    .error {
        text-align: center;
        color: red;
    }

    .rightSide{
        border: 1px solid #bdbdbd;
        position: relative;
        padding: 10px;
    }

    .leftSide{
        position: relative;
    }

    i{
        cursor: pointer;
    }

    .groups{
        width: 100%;
        display: block;
        margin: 0 auto;
        border: 1px solid black;
        margin-bottom: 25px;
    }

    .hide{
        display: none;
    }

    .question{
        margin-top: 25px;
    }

    .question-title{
        font-size: 1.25em;
        font-weight: 100;
    }

    .question-required-mark{
        max-width: 100px;
        text-align: center;
    }

    .question-answer{
        max-width: 200px;
        text-align: center;
    }

    .question-answer input, .question-answer i{
        margin-right: 25px;
    }

    .explanation, .comment{
        max-height: 250px;
        overflow-y: auto;
    }

    .header-info{
        width: 80%;
        display: block;
        margin: 0 auto;
        text-align: right;
        padding-right: 80px;
    }

    .allowNewLine{
        white-space: pre-line;
    }

    .pd {
        position: absolute;
        right: 0vw;
        margin-top: -1vh;
    }

    .submitExam {
        float: right;
        position: relative;
        bottom: 2vw;
        border: 1px solid darkgray;
        background-color: #eaeaea;
        border-radius: 3px;
        padding: 5px 10px;
    }

    @media (max-width: 1120px) {
        .submitExam {
            position: relative;
            right: 5px;
            bottom: 35px;
            float: right;
        }
    }
</style>
