import Vuex from 'vuex';
import Vue from 'vue';
import StoreData from './store';
import ExaminerHome from './components/examiner/ExaminerHome.vue';
import Login from './components/auth/Login.vue';
import Register from './components/auth/Register.vue';
import ExamSheet from './components/examiner/ExamSheet.vue';
import ExamPreview from './components/examiner/ExamPreview.vue';
import ExamCombine from './components/examiner/ExamCombine.vue';
import ExamCreator from './components/admin/ExamCreator.vue';
import ManageDomains from './components/ceo/ManageDomains.vue';
import Admin from './components/admin/Admin.vue';
import AdminExams from './components/admin/AdminExams.vue';
import AdminStudents from './components/admin/AdminStudents.vue';
import AdminPlanStudents from './components/admin/AdminPlanStudents.vue';
import SuperAdminManageAdmins from './components/admin/ManageAdmins.vue';
import SuperAdminManageEducations from './components/superAdmin/ManageEducations.vue';
import InviteUsers from './components/Admin/InviteUsers.vue';
import Students from './components/students/Students.vue';
import StudentResult from './components/students/StudentResult.vue';
import AdminLog from './components/admin/AdminLog.vue';

Vue.use(Vuex);

const store = new Vuex.Store(StoreData);

// Roles:
// 1 = CEO - Slemmer (Klant)
// 2 = SuperAdmin - Mr. de Jong (hoofd van domain)
// 3 = Admin - De docenten die over de examens gaan en alles regelen
// 4 = Examiner - Alle overige docenten

export const routes = [
    {
        name: '',
        path: '',
        redirect: '/examinerhome'
    },
    {
        name: 'AdminHome',
        path: '/adminhome',
        meta: {
            title: 'Home',
            roles: ["admin", "superAdmin", "ceo"],
            requireInvitationToken: false
        },
        component: Admin
    },
    {
        name: 'AdminExams',
        path: '/adminexams',
        meta: {
            title: 'Admin Exams',
            roles: ["admin", "superAdmin"],
            requireInvitationToken: false
        },
        component: AdminExams
    },
    {
        name: 'PlanStudents',
        path: '/planstudents',
        meta: {
            title: 'Plan Students',
            roles: ["admin", "superAdmin"],
            requireInvitationToken: false
        },
        component: AdminPlanStudents
    },
    {
        name: 'Log',
        path: '/log',
        meta: {
            title: 'Admin Log',
            roles: ["admin", "superAdmin", "ceo"],
            requireInvitationToken: false
        },
        component: AdminLog
    },
    {
        name: 'AdminStudents',
        path: '/adminstudents',
        meta: {
            title: 'Admin Students',
            roles: ["admin"],
            requireInvitationToken: false
        },
        component: AdminStudents
    },
    {
        name: 'ManageEducations',
        path: '/ManageEducations',
        meta: {
            title: 'Manage Educations',
            roles: ["superAdmin"],
            requireInvitationToken: false
        },
        component: SuperAdminManageEducations
    },
    {
        name: 'ManageAdmins',
        path: '/ManageAdmins',
        meta: {
            title: 'Manage Admins',
            roles: ["superAdmin", "ceo", "admin"],
            requireInvitationToken: false
        },
        component: SuperAdminManageAdmins
    },
    {
        name: 'ManageDomains',
        path: '/managedomains',
        meta: {
            title: 'Manage Domains',
            roles: ["ceo"],
            requireInvitationToken: false
        },
        component: ManageDomains
    },
    {
        name: 'InviteUsers',
        path: '/InviteUsers',
        meta: {
            title: 'Invite Users',
            roles: ["ceo", "superAdmin", "admin"],
            requireInvitationToken: false
        },
        component: InviteUsers
    },
    {
        name: 'ExaminerHome',
        path: '/examinerhome',
        meta: {
            title: 'ExaminerHome',
            roles: ["examiner"],
            requireInvitationToken: false
        },
        component: ExaminerHome
    },
    {
        name: 'Login',
        path: '/login',
        meta: {
            title: 'Login',
            roles: [],
            requireInvitationToken: false
        },
        component: Login
    },
    {
        name: 'Register',
        path: '/register',
        meta: {
            title: 'Register',
            roles: [],
            requireInvitationToken: true
        },
        component: Register
    },
    {
        name: 'Students',
        path: '/students',
        meta: {
            title: 'Students',
            roles: ["examiner", "superAdmin", "admin"],
            requireInvitationToken: false
        },
        component: Students
    },
    {
        name: 'StudentResult',
        path: '/studentresult/:resultId',
        props: true,
        meta: {
            title: 'StudentResult',
            roles: ["examiner", "superAdmin", "admin"],
            requireInvitationToken: false
        },
        component: StudentResult
    },
    {
        name: 'ExamSheet',
        path: '/examsheet/:id',
        props: true,
        meta: {
            title: 'Sheet',
            roles: ["examiner"],
            requireInvitationToken: false
        },
        component: ExamSheet
    },
    {
        name: 'ExamPreview',
        path: '/exampreview/:id',
        props: true,
        meta: {
            title: 'ExamPreview',
            roles: ["examiner"],
            requireInvitationToken: false
        },
        component: ExamPreview
    },
    {
        name: 'ExamCreator',
        path: '/examcreator',
        meta: {
            title: 'ExamCreator',
            roles: ["superAdmin", "admin"],
            requireInvitationToken: false
        },
        component: ExamCreator
    },
    {
        name: 'ExamEditor',
        path: '/exameditor/:id',
        props: true,
        meta: {
            title: 'ExamEditor',
            roles: ["superAdmin", "admin"],
            requireInvitationToken: false
        },
        component: ExamCreator
    },
    {
        name: 'ExamCombine',
        path: '/examcombine/:instanceId',
        props: true,
        meta: {
            title: 'ExamCombine',
            roles: ["examiner"],
            requireInvitationToken: false
        },
        component: ExamCombine
    }
];
