<template>
    <div>
        <div v-for="(group, groupIndex) in groups" class="groups allowNewLine" id="tableBackground">
            <p class="question-title">{{group.name}}:</p>
            <p class="header-info pd">Perceived, Doubt</p>
            <div v-for="(question, questionIndex) in group.questions">
                <div class="question row">
                    <p class="col question-required-mark">({{cesuurs.marks[question.requiredMark]}})</p>
                    <p class="col">
                        <i v-on:click="toggleQuestion(groupIndex + '-' + questionIndex + '_question')" style="cursor:pointer;" class="fas fa-info-circle"></i>
                        {{question.question}}
                    </p>
                    <div class="col question-answer">
                        <input type="checkbox" disabled>
                        <input type="checkbox" disabled>
                        <i class="fas fa-comment"></i>
                    </div>
                </div>
                <p :id="groupIndex + '-' + questionIndex + '_question'" class="explanation hide col-sm-12">{{question.explanation}}</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "examPreview",
    props: ['id'],
    data() {
        return {
            error: null,
            groups:  {},
            cesuurs: {},
            prevQuestionId: null,
            prevCommentId: null
       };
    },
    computed: {
        currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
                set: function () {
                }
            },
    },
    methods: {
        checkUser() {
            if (this.currentUser.role_id === 1) {
                this.$router.push({path: '/admin'});
            }
        },
        getExam(){
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.id)
                .then((response) => {
                    console.log(JSON.parse(response.data.template));
                    this.groups = JSON.parse(response.data.template)['groups'];
                    this.cesuurs = JSON.parse(response.data.template)['cesuur'];
                })
                .catch((err) =>{
                    alert('Cannot get exam.')
                })
            });
        },
        toggleQuestion(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevQuestionId != null && this.prevQuestionId != id){
                document.getElementById(this.prevQuestionId).classList.add('hide');
            }
            this.prevQuestionId = id;
        }

    },
    mounted(){
        this.checkUser();
        this.getExam();
    },
}
</script>

<style scoped>
    .error {
        text-align: center;
        color: red;
    }

    .groups{
        width: 80%;
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

    .allowNewLine {
        white-space: pre-line;
    }

    .pd {
        position: absolute;
        right: 10vw;
        margin-top: -1vh;
    }
</style>
