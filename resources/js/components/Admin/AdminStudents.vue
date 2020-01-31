<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row flex-wrap d-inline justify-content-around mt-2">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Upload Student</h5>
                    <div class="form-group">
                        Please select a csv file containing a students list.
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="csv_file" @change="loadCSV($event)" accept=".csv">
                            <label class="custom-file-label" for="csv_file">Choose file</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" name="button" v-on:click="uploadConfirm" v-if="parse_csv.length > 0">Confirm</button>
                        <select v-if="parse_csv.length > 0" class="custom-select" style="width: 175px;" v-model="selectedEducationId">
                            <option v-if="selectedEducationId == -1" value="-1" selected>Select an education</option>
                            <option v-for="education in educations" :value="education.id">{{education.name}}</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table v-if="parse_csv.length > 0" class="table table-hover table-striped table-large">
                            <thead>
                                <tr>
                                    <th style="width: 10%">#</th>
                                    <th v-for="key in parse_header" :class="{ active: sortKey == key }" style="text-transform: capitalize;">
                                        {{key}}
                                        <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'">
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(csv, csvIndex) in parse_csv">
                                    <th>{{csvIndex+1}}</th>
                                    <td v-for="(key, index) in csv">
                                        {{key}}
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
   import { getRoleByString } from "../../helpers/roles";
   export default {
    data() {
        return {
            error: null,

            studentsList: [],
            parse_header: [],
            parse_csv: [],
            csv: null,
            educations: [],
            selectedEducationId: -1,

            sortOrders:{},
            sortKey: '',
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
        back(){
            this.$router.push('adminhome');
        },
        getEducations() {
            new Promise((res, rej) => {
                axios.post('/api/students/geteducations', {'domain_id': this.currentUser.domain_id})
                .then((response) => {
                    if(this.currentUser.role_id == getRoleByString("superAdmin")){
                        this.educations = response.data;
                    }else{
                        for (var i = 0; i < response.data.length; i++) {
                            if(response.data[i].id == this.currentUser.education_id){
                                this.educations.push(response.data[i]);
                            }
                        }
                    }
                })
                .catch((err) => {
                    self.createLog(self.currentUser, `Tried to load educations but an error occured`);
                    self.$noty.error("Cannot get educations.");
                })
            });
        },
        csvJSON(csv){
            var vm = this;
            var lines = csv.split("\n");
            var result = [];
            var headers = lines[0].split(";");
            vm.parse_header = lines[0].split(";");
            lines[0].split(";").forEach(function (key) {
                vm.sortOrders[key] = 1;
            });
            lines.map(function(line, indexLine){
                if (indexLine < 1) return; // Jump header line
                var obj = {};
                var currentline = line.split(";");
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
                this.$noty.warning("No file selected");
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
                            this.$noty.error("Cannot read file!");
                        }
                    };
                } else {
                    this.$noty.error('FileReader are not supported in this browser.')
                }
            }
        },
        uploadConfirm() {
            var self = this;
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
                    newStudent['domain_id'] = this.currentUser.domain_id;
                    dataToUpload.push(newStudent);
                }
            }
            if (dataToUpload.length > 0) {
                new Promise((res, rej) => {
                    axios.post('/api/admin/upload', {'data':JSON.stringify(dataToUpload)})
                    .then((response) => {
                        if (response.data == "invalid") {
                            self.$noty.error("Please upload a valid CSV file");
                        } else {
                            self.parse_header = [];
                            self.parse_csv = [];
                            self.csv = null;
                            document.getElementById("csv_file").value = null;
                            self.$noty.success("Upload successfull!");
                            self.createLog(this.currentUser, `Uploaded a studentlist`);
                            self.$router.push('/students');
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                        self.$noty.error("Please upload a valid CSV file");
                    })
                });
            } else {
                self.$noty.error("No new students found!");
            }
            this.getStudents();
        },
        getStudents() {
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/admin/getStudents', {id: this.currentUser.school_id})
                .then((response) => {
                    this.studentsList = response.data;
                })
                .catch((error) => {
                    self.createLog(self.currentUser, `Tried to load students but an error occured`);
                    self.$noty.error("Error occured while fetching studentsList");
                });
            });
        },
    },
    mounted() {
        this.getStudents();
        this.getEducations();
    }
}
</script>
<style scoped>
.card{
    width: 100%;
}
</style>
