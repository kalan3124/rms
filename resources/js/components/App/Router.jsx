import React, { Component, lazy } from 'react';
import { connect } from 'react-redux';
import  BrowserRouter  from "react-router-dom/BrowserRouter";
import { APP_DIRECTORY } from '../../constants/config';
import {
    guestAccess,
    loadUser
} from '../../actions/App';

import  Route  from 'react-router-dom/Route';
import  Switch  from 'react-router-dom/Switch';

// Pre loading components
import LoadingPage from './LoadingPage';
import LoginForm from '../LoginForm/LoginForm';
import DashBoard from '../DashBoard/DashBoard';
import CrudPage from '../CrudPage/CrudPage';
import Empty from './Empty';
import AsyncComponent from './AsyncComponent';

const Permission = ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "permission" */'../Permission/Permission'))}/>);
const IssueTracker = ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "issue-tracker" */"../IssueTracker/IssueTracker"))}/>);
const UserChangePassword = ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "change-password" */"../Password/UserChangePassword"))}/>);

//Lazy Loading components
const medical ={
    ItineraryPage: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-itinerary" */'../Medical/ItineraryPage/ItineraryPage'))} />),
    StandardItineraryPage: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-standard-itinerary" */'../Medical/StandardItinerary/StandardItinerary'))} />),
    DoctorTimeTable: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-doctor-time-table" */'../Medical/DoctorTimeTable/DoctorTimeTable'))}/>),
    TeamProductsAllocations: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-team-products-allocations" */'../Medical/TeamProductsAllocations/TeamProductsAllocations'))}/>),
    Report: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-report" */'../Medical/Report/Report'))}/>),
    DoctorProducts: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-doctor-products" */'../Medical/DoctorProducts/DoctorProducts'))}/>),
    GPSTracking: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-gps-tracking" */'../Medical/GPSTracking/GPSTracking'))}/>),
    UserArea: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-user-area" */'../Medical/UserArea/UserArea'))}/>),
    UserCustomer: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-user-customer" */'../Medical/UserCustomer/UserCustomer'))}/>),
    TeamProduct: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-team-product" */'../Medical/TeamProduct/TeamProduct'))}/>),
    ItineraryApproval: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-itinerary-approval" */"../Medical/ItineraryApproval/ItineraryApproval"))}/>),
    UploadCSV: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-upload-csv" */"../Medical/UploadCSV/UploadCSV"))}/>),
    Target: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-target" */"../Medical/Target/Target"))}/>),
    DoctorApprove: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-doctor-approve" */"../Medical/DoctorApprove/DoctorApprove"))}/>),
    ExpenceStatement: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-expence-statement" */"../Medical/Reports/ExpenceStatement/ExpenceStatement"))}/>),
    ItineraryViewer: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-itinerary-viewer" */"../Medical/ItineraryViewer/ItineraryViewer"))}/>),
    FmLevelReport: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-fm-level-report" */"../Medical/Reports/FmLevelReport/FmLevelReport"))}/>),
    MrPsItineraryReport: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-mr-ps-itinerary-report" */"../Medical/Reports/MrPsItineraryReport/MrPsItineraryReport"))}/>),
    YtdSalesSheetReport: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-ytd-sales-sheet-report" */"../Medical/Reports/YtdSalesSheetReport/YtdSalesSheetReport"))}/>),
    UserClone: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-user-clone" */"../Medical/UserClone/UserClone"))}/>),
    DoctorTown: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-doctor-town" */"../Medical/DoctorTown/DoctorTown"))} /> ),
    SalesAllocatioin: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-allocation" */"../Medical/SalesAllocatioin/SalesAllocatioin"))} /> ),
    InvoiceAllocation: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "invoice-allocation" */"../Medical/InvoiceAllocation/InvoiceAllocation"))} /> ),
    TeamPerformanceReport: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-Team-Performance-report" */"../Medical/Reports/TeamPerformanceReport/TeamPerformanceReport"))}/>),
    HodGpsTracking: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-hod-gps-tracking" */"../Medical/HodGpsTracking/HodGpsTracking"))}/>),
    UserTeamAllocation: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "user-team-allocation" */"../Medical/UserTeamAllocation/UserTeamAllocation"))} />),
    UserChangePasswordNew: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "change-password-new" */"../Medical/Password/UserChangePassword"))}/>)
}

const sales = {
    RouteChemist: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-route-chemist" */"../Sales/RouteChemist/RouteChemist"))} /> ),
    ItineraryPage: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-itinerary-page" */"../Sales/ItineraryPage/ItineraryPage"))} /> ),
    SalesItineraryApproval: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-itinerary-approval" */"../Sales/SalesItineraryApproval/SalesItineraryApproval"))} /> ),
    TownWiseSales: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "town-wise-sales-report" */"../Sales/TownWiseSales/TownWiseSales"))} /> ),
    SalesGPSTracking: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-gps-tracking" */'../Sales/GPSTracking/GPSTracking'))}/>),
    SalesTarget: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-target" */'../Sales/Target/Target'))}/>),
    WeeklyTarget: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "weekly-target" */'../Sales/WeeklyTarget/WeeklyTarget'))}/>),
    ExpensesEdit: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "expnses-edit" */'../Sales/ExpensesEdit/ExpensesEdit'))}/>),
    DCCustomerAllocation: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "expnses-edit" */'../Sales/DCCustomerAllocation/DCCustomerAllocation'))}/>),
    DCItineraryPage: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "dc-itinerary-page" */"../Sales/DCItineraryPage/DCItineraryPage"))} /> ),
    Competitors: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "Competitors" */"../Sales/Competitors/Competitors"))} /> ),
}

const distributor = {
    SRCustomerAllocation:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sr-customer-allocation" */"../Distributor/SRCustomerAllocation/SRCustomerAllocation"))} /> ),
    PurchaseOrder:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "purchase-order" */"../Distributor/PurchaseOrder/PurchaseOrder"))} />),
    PurchaseOrderConfirm:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "purchase-order-confirm" */"../Distributor/PurchaseOrderConfirm/PurchaseOrderConfirm"))} />),
    SrAllocation:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sr-allocation" */"../Distributor/SrAllocation/SrAllocation"))} />),
    SiteAllocation:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "site-allocation" */"../Distributor/SiteAllocation/SiteAllocation"))} />),
    SRProductAllocation:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sr-product-allocation" */"../Distributor/SRProductAllocation/SRProductAllocation"))} /> ),
    CreateInvoice:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "create-invoice" */"../Distributor/CreateInvoice/CreateInvoice"))} />),
    SalesmanAllocation:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "salesman-allocation" */"../Distributor/SalesmanAllocation/SalesmanAllocation"))} />),
    StockAdjusment:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "stock-adjusment" */"../Distributor/StockAdjusment/StockAdjusment"))} />),
    DirectInvoice:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "direct-invoice" */"../Distributor/DirectInvoice/DirectInvoice"))} />),
    GRNConfirm:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "grn-confirm" */"../Distributor/GRNConfirm/GRNConfirm"))} />),
    CreateReturn:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "create-return" */"../Distributor/CreateReturn/CreateReturn"))} />),
    SalesProcess:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "sales-process" */"../Distributor/SalesProcess/SalesProcess"))} />),
    OrderBasedReturn:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "order-based-return" */"../Distributor/OrderBasedReturn/OrderBasedReturn"))} />),
    InvoicePayment:()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "order-based-return" */"../Distributor/CreatePayment/InvoicePayment"))} />),
    CompanyReturn: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "company-return" */"../Distributor/CompanyReturn/CompanyReturn"))} />)
}

const mapStateToProps = state => ({
    ...state,
    ...state.App
})

class Router extends Component {

    componentDidMount() {
        const { dispatch } = this.props;
        // Loading user on Router loaded
        if (localStorage.getItem('userToken')) {
            dispatch(loadUser(localStorage.getItem('userToken')));
        } else {
            dispatch(guestAccess());
        }
    }

    render() {
        const { user, userLoading } = this.props;

        return (
            <BrowserRouter basename={APP_DIRECTORY}>
                {userLoading ?
                    <LoadingPage/>
                    :
                    (user ?
                        <div>
                            <Switch>
                                <Route exact={true} path="/" component={DashBoard} />
                                <Route exact path="/:type/:menu/permission" component={Permission} />
                                <Route path="/:type/:menu/panel/:form/:mode?" component={CrudPage} />
                                <Route exact path="/:type/:menu/issues" component={IssueTracker}/>

                                {/* Medical app routes */}
                                <Route exact path="/:type/:menu/individual" component={medical.ItineraryPage} />
                                <Route exact path="/:type/:menu/standard" component={medical.StandardItineraryPage} />
                                <Route exact path="/:type/:menu/doc_time_table" component={medical.DoctorTimeTable} />
                                <Route exact path="/:type/:menu/team_member_products_allocations" component={medical.TeamProductsAllocations} />
                                <Route exact path="/:type/:menu/doc_products" component={medical.DoctorProducts} />
                                <Route exact path="/:type/:menu/report/:report" component={medical.Report} />
                                <Route exact path="/:type/:menu/gps_tracking" component={medical.GPSTracking} />
                                <Route exact path="/:type/:menu/itinerary_viewer" component={medical.ItineraryViewer} />
                                <Route exact path="/:type/:menu/user_area" component={medical.UserArea} />
                                <Route exact path="/:type/:menu/user_customer" component={medical.UserCustomer} />
                                <Route exact path="/:type/:menu/team_products_allocations" component={medical.TeamProduct} />
                                <Route exact path="/:type/:menu/individual_approval" component={medical.ItineraryApproval} />
                                <Route exact path="/:type/:menu/upload_csv/form/:formName" component={medical.UploadCSV}/>
                                <Route exact path="/:type/:menu/upload_csv/:name" component={medical.UploadCSV}/>
                                <Route exact path="/:type/:menu/target" component={medical.Target}/>
                                <Route exact path="/:type/:menu/doc_approve" component={medical.DoctorApprove}/>
                                <Route exact path="/:type/:menu/exp_statement" component={medical.ExpenceStatement}/>
                                <Route exact path="/:type/:menu/fm_level_report" component={medical.FmLevelReport} />
                                <Route exact path="/:type/:menu/mr_ps_itinerary_report" component={medical.MrPsItineraryReport} />
                                <Route exact path="/:type/:menu/ytd_sales_sheet_report" component={medical.YtdSalesSheetReport} />
                                <Route exact path="/:type/:menu/user_clone" component={medical.UserClone} />
                                <Route exact path="/:type/:menu/doctor_town" component={medical.DoctorTown} />
                                <Route exact path="/:type/:menu/sales_allocation" component={medical.SalesAllocatioin} />
                                <Route exact path="/:type/:menu/invoice_allocation" component={medical.InvoiceAllocation} />
                                <Route exact path="/:type/:menu/team_performance" component={medical.TeamPerformanceReport} />
                                <Route exact path="/:type/:menu/sales_process" component={distributor.SalesProcess} />
                                <Route exact path="/:type/:menu/hod_gps_tracking" component={medical.HodGpsTracking} />
                                <Route exact path="/:type/:menu/user_team_allocation" component={medical.UserTeamAllocation} />
                                <Route exact path="/:type/:menu/user_acc" component={medical.UserChangePasswordNew} />

                                {/* Sales app routes */}
                                <Route exact path="/:type/:menu/route_chemist/:mode" component={sales.RouteChemist} />
                                <Route exact path="/:type/:menu/itinerary_page/:mode" component={sales.ItineraryPage} />
                                <Route exact path="/:type/:menu/sale_itinerary_approval/:mode" component={sales.SalesItineraryApproval} />
                                <Route exact path="/:type/:menu/town_wise_sales" component={sales.TownWiseSales} />
                                <Route exact path="/:type/:menu/sales_gps_tracking" component={sales.SalesGPSTracking} />
                                <Route exact path="/:type/:menu/sales_target" component={sales.SalesTarget} />
                                <Route exact path="/:type/:menu/week_target" component={sales.WeeklyTarget} />
                                <Route exact path="/:type/:menu/expenses_edit" component={sales.ExpensesEdit} />
                                <Route exact path="/:type/:menu/dc_customer" component={sales.DCCustomerAllocation} />
                                <Route exact path="/:type/:menu/dc_itinerary_page" component={sales.DCItineraryPage} />
                                <Route exact path="/:type/:menu/competitors" component={sales.Competitors} />

                                {/** Distributor routes */}
                                <Route exact path="/:type/:menu/sr_customer_allocation" component={distributor.SRCustomerAllocation} />
                                <Route exact path="/:type/:menu/purchase_order" component={distributor.PurchaseOrder} />
                                <Route exact path="/:type/:menu/purchase_order_confirm" component={distributor.PurchaseOrderConfirm} />
                                <Route exact path="/:type/:menu/purchase_order_confirm/:number" component={distributor.PurchaseOrderConfirm} />
                                <Route exact path="/:type/:menu/sr_allocation" component={distributor.SrAllocation} />
                                <Route exact path="/:type/:menu/site_allocation" component={distributor.SiteAllocation} />
                                <Route exact path="/:type/:menu/sr_product_allocation" component={distributor.SRProductAllocation} />
                                <Route exact path="/:type/:menu/create_invoice/:number?" component={distributor.CreateInvoice} />
                                <Route exact path="/:type/:menu/salesman_allocation" component={distributor.SalesmanAllocation} />
                                <Route exact path="/:type/:menu/stock_adjusment" component={distributor.StockAdjusment} />
                                <Route exact path="/:type/:menu/direct_invoice" component={distributor.DirectInvoice} />
                                <Route exact path="/:type/:menu/grn_confirm/:number?" component={distributor.GRNConfirm} />
                                <Route exact path="/:type/:menu/create_return" component={distributor.CreateReturn} />
                                <Route exact path="/:type/:menu/order_based_return" component={distributor.OrderBasedReturn} />
                                <Route exact path="/:type/:menu/invoice_payment/:number?" component={distributor.InvoicePayment} />
                                <Route exact path="/:type/:menu/company_return" component={distributor.CompanyReturn} />
                                <Route exact path="/change_password" component={UserChangePassword}/>
                                <Route path="*" exact={true} component={Empty} />

                            </Switch>
                        </div>
                        :
                        <div>
                            <Route path='/' component={LoginForm} />
                        </div>
                    )
                }
            </BrowserRouter>
        )
    }
}

export default connect(mapStateToProps)(Router);
