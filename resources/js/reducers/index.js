import { combineReducers } from 'redux';
import App from './App';
import LoginForm from './LoginForm';
import Header from './Header';
import Layout from './Layout';
import Sidebar from './Sidebar';
import CrudPage from './CrudPage';
import CrudForm from './CrudForm';
import Dialogs from './Dialogs';
import Dashboard from './Dashboard';
import ItineraryPage from './Medical/ItineraryPage';
import StandardItineraryPage from './Medical/StandardItineraryPage';
import DoctorTimeTable from './Medical/DoctorTimeTable'
import TeamProductAllocations from './Medical/TeamProductAllocations'
import Report from './Medical/Report'
import GPSTracking from './Medical/GPSTracking'
import UserArea from './Medical/UserArea'
import UserCustomer from './Medical/UserCustomer';
import Permission from './Permission';
import TeamProduct from "./Medical/TeamProduct";
import ItineraryApproval from "./Medical/ItineraryApproval";
import UploadCSV from "./Medical/UploadCSV";
import IssueTracker from "./IssueTracker";
import Target from "./Medical/Target";
import DoctorApprove from "./Medical/DoctorApprove";
import ExpenceStatement from './Medical/ExpenceStatement';
import ItineraryViewer from './Medical/ItineraryViewer';
import FmLevelReport from './Medical/FmLevelReport';
import MrPsItineraryReport from './Medical/MrPsItineraryReport';
import YtdSalesSheetReport from './Medical/YtdSalesSheetReport';
import UserClone from './Medical/UserClone';
import DoctorTown from './Medical/DoctorTown';
import UserAllocation from './Medical/UserAllocation';
import SalesAllocation from './Medical/SalesAllocation';
import InvoiceAllocation from './Medical/InvoiceAllocation';
import TeamPerformanceReport from './Medical/TeamPerformanceReport';
import HodGpsTracking from './Medical/HodGpsTracking';
import UserTeamAllocation from './Medical/UserTeamAllocation';

import RouteChemist from './Sales/RouteChemist';
import SalesItineraryPage from './Sales/ItineraryPage';
import SalesItineraryApproval from './Sales/SalesItineraryApproval';
import TownWiseSales from './Sales/TownWiseSales';
import SalesGPSTracking from './Sales/GPSTracking';
import SalesTarget from './Sales/Target';
import WeeklyTarget from './Sales/WeeklyTarget';
import ExpensesEdit from './Sales/ExpensesEdit';
import Competitors from './Sales/Competitors';

import SrCustomerAllocation from './Distributor/SRCustomerAllocation';
import SrProductAllocation from './Distributor/SRProductAllocation';
import PurchaseOrder from './Distributor/PurchaseOrder';
import PurchaseOrderConfirm from './Distributor/PurchaseOrderConfirm';
import SrAllocation from './Distributor/SrAllocation';
import SiteAllocation from './Distributor/SiteAllocation';
import CreateInvoice from './Distributor/CreateInvoice';
import SalesmanAllocation from './Distributor/SalesmanAllocation';
import StockAdjusment from './Distributor/StockAdjusment';
import DirectInvoice from './Distributor/DirectInvoice';
import GRNConfirm from './Distributor/GRNConfirm'
import CreateReturn from './Distributor/CreateReturn';
import SalesProcess from './Distributor/SalesProcess';
import OrderBasedReturn from './Distributor/OrderBasedReturn';
import CompanyReturn from './Distributor/CompanyReturn';

import UserChangePassword from './Medical/UserChangePassword';


import DCCustomerAllocation from './Sales/DCCustomerAllocation';
import DCItineraryPage from './Sales/DCItineraryPage';
import InvoicePayment from './Distributor/InvoicePayment';

export default combineReducers({
    App,
    LoginForm,
    Header,
    Layout,
    Sidebar,
    CrudPage,
    CrudForm,
    Dialogs,
    Dashboard,
    ItineraryPage,
    StandardItineraryPage,
    DoctorTimeTable,
    TeamProductAllocations,
    Report,
    GPSTracking,
    UserArea,
    UserCustomer,
    Permission,
    TeamProduct,
    ItineraryApproval,
    UploadCSV,
    IssueTracker,
    Target,
    DoctorApprove,
    ExpenceStatement,
    ItineraryViewer,
    FmLevelReport,
    MrPsItineraryReport,
    YtdSalesSheetReport,
    UserClone,
    DoctorTown,
    UserAllocation,
    SalesAllocation,
    InvoiceAllocation,
    TeamPerformanceReport,
    HodGpsTracking,
    UserTeamAllocation,

    RouteChemist,
    SalesItineraryPage,
    SalesItineraryApproval,
    TownWiseSales,
    SalesGPSTracking,
    SalesTarget,
    WeeklyTarget,
    ExpensesEdit,
    Competitors,

    SrCustomerAllocation,
    PurchaseOrder,
    PurchaseOrderConfirm,
    SrAllocation,
    SiteAllocation,
    SrProductAllocation,
    CreateInvoice,
    SalesmanAllocation,
    StockAdjusment,
    DirectInvoice,
    GRNConfirm,
    CreateReturn,
    SalesProcess,
    OrderBasedReturn,
    CompanyReturn,

    UserChangePassword,

    DCCustomerAllocation,
    DCItineraryPage,
    InvoicePayment,
    UserChangePassword
})
