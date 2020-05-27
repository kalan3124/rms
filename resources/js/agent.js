import { APP_URL, DISTRIBUTOR_SALES_REP_TYPE } from './constants/config';

const responseBody = res => res.data;

const requests = {
    del: url =>
        axios.del(APP_URL + 'api/web/' + url).then(responseBody),
    get: url =>
        axios.get(APP_URL + 'api/web/' + url).then(responseBody),
    put: (url, body) =>
        axios.put(APP_URL + 'api/web/' + url, body).then(responseBody),
    post: (url, body) =>
        axios.post(APP_URL + 'api/web/' + url, body).then(responseBody)
};

const App = {
    sidebar: () =>
        requests.post('sidebar'),
    dashboard: () =>
        requests.post('sidebar/main')
}

const Auth = {
    login: (username, password, remember, newPassword) =>
        requests.post('login', { username, password, remember, newPassword }),
    check: () =>
        requests.post('user'),
    change: (password,lock_time,attempts) =>
        requests.post('user_change', { password,lock_time,attempts }),
    loadOther: () =>
        requests.post('user_other'),
    changeOther: (lock_time,attempts) =>
        requests.post('user_other_new', { lock_time,attempts }),
}

const Crud = {
    info: (form) =>
        requests.post('panel/' + form + '/info'),
    search: (form, values, sortBy, sortMode, page, perPage) =>
        requests.post('panel/' + form + '/search', { values, sortBy, sortMode, page, perPage }),
    csv: (form, values, sortBy, sortMode, page, perPage) =>
        requests.post('panel/' + form + '/csv', { values, sortBy, sortMode, page, perPage }),
    xlsx: (form, values, sortBy, sortMode, page, perPage) =>
        requests.post('panel/' + form + '/xlsx', { values, sortBy, sortMode, page, perPage }),
    pdf: (form, values, sortBy, sortMode, page, perPage) =>
        requests.post('panel/' + form + '/pdf', { values, sortBy, sortMode, page, perPage }),
    create: (form, values) =>
        requests.post('panel/' + form + '/create', { values }),
    update: (form, values, id) =>
        requests.post('panel/' + form + '/update', { values, id }),
    dropdown: (form, keyword, where, limit) =>
        requests.post('panel/' + form + '/dropdown', { keyword, where, limit }),
    delete: (form, id) =>
        requests.post('panel/' + form + '/delete', { id }),
    restore: (form, id) =>
        requests.post('panel/' + form + '/restore', { id })
}

const Itinerary = {
    load: (yearMonth, mr, fm) =>
        requests.post('itinerary/load', { yearMonth, mr, fm }),
    dayTypes: () =>
        requests.post('itinerary/dayTypes'),
    save: (dates, yearMonth, mr, fm) =>
        requests.post('itinerary/save', { dates, yearMonth, mr, fm })
}

const StandardItinerary = {
    save: (rep, dates) =>
        requests.post('standard_itinerary/save', { rep, dates }),
    load: rep =>
        requests.post('standard_itinerary/load', { rep })
}

const TimeTable = {
    load: doc =>
        requests.post('doc_time_table/load', { doc }),
    save: (doc, shedules) =>
        requests.post('doc_time_table/save', { doc, shedules })
}

const TeamProductAllocations = {
    load: team =>
        requests.post('product_allocations/load', { team }),
    save: (team, allocated) =>
        requests.post('product_allocations/save', { team, allocated })
}

const Report = {
    search: (report, searchParameters) =>
        requests.post('report/' + report + '/search', searchParameters),
    info: report =>
        requests.post('report/' + report + '/info'),
    saveAsFile: (format, report, searchParameters) =>
        requests.post('report/' + report + '/' + format, searchParameters)
}

const GPS = {
    search: (user, date) =>
        requests.post('gps/search', { user, date })
}

const UserArea = {
    territoryLevels: () =>
        requests.post('user_area/levels'),
    userInfo: user =>
        requests.post('user_area/load', { user }),
    create: (user, area) =>
        requests.post('user_area/create', { user, area }),
    remove: (user, area) =>
        requests.post('user_area/remove', { user, area }),
    removeAll: (user) =>
        requests.post('user_area/remove_all', { user }),
}

const UserCustomer = {
    loadByUser: user =>
        requests.post('user_customer/load', { user }),
    create: (user, customer, type) =>
        requests.post('user_customer/create', { user, customer, type }),
    remove: (user, customer, type) =>
        requests.post("user_customer/remove", { user, customer, type }),
    removeAll: (user) =>
        requests.post("user_customer/remove_all", { user })
}

const Permission = {
    load: () =>
        requests.post('permissions/load'),
    save: (users, permissionGroups, permissionValues) =>
        requests.post('permissions/save', { users, permissionGroups, permissionValues }),
    loadByUser: (user, type) =>
        requests.post("permissions/loadByUser", { user, type })
}

const TeamProduct = {
    save: (teams, products) =>
        requests.post('team_products/save', { teams, products }),
    load: (team) =>
        requests.post('team_products/load', { team }),
    loadPrincipal: (principal) =>
        requests.post('team_products/load_product_by_Principal', { principal })
}

const ItineraryApproval = {
    search: (division, team, type) =>
        requests.post('itinerary/approval/search', { division, team, type }),
    approve: id =>
        requests.post('itinerary/approval/approve', { id })
};

const UploadCSV = {
    info: (params) =>
        requests.post('upload_csv/info', {...params }),
    generateFormat: (params) =>
        requests.post('upload_csv/format', {...params }),
    submit: (type, name, fileName) =>
        requests.post('upload_csv/submit', { type, name, fileName }),
    status: () =>
        requests.post('upload_csv/status')
}

const IssueTracker = {
    create: (content, label) =>
        requests.post('issue/create', { content, label }),
    search: (state, page) =>
        requests.post('issue/search', { state, page }),
    upload: (file) => {
        let formData = new FormData();

        formData.append("file", file);

        return requests.post('upload/image', formData)
    }
}

const Target = {
    load: (rep, month) =>
        requests.post('target/load', { rep, month }),
    save: (rep, mainValue, mainQty, products, brands, principals, month) =>
        requests.post('target/save', { rep, mainValue, mainQty, products, brands, principals, month })
}

const DoctorApprove = {
    search: (user, toDate, fromDate) =>
        requests.post('doc_approve/search', { user, toDate, fromDate }),
    submit: (key, values) =>
        requests.post('doc_approve/save', { key, values }),
    delete: (key) =>
        requests.post('doc_approve/delete', { key })
}

const ExpenceStatement = {
    search: (values) =>
        requests.post('report/exp_statement/search', { values }),
    types: () =>
        requests.post('report/exp_statement/types')
}

const ItineraryViewer = {
    search: (user, month) => requests.post('itinerary_viewer/search', { user, month }),
    load: (id) => requests.post('itinerary_viewer/load', { id })
}

const FmLevelReport = {
    search: (values) =>
        requests.post('report/fm_level_report/search', { values })
}

const MrPsItineraryReport = {
    search: (values) =>
        requests.post('report/mr_ps_report/search', { values })
}

const YtdSalesSheetReport = {
    search: (values) =>
        requests.post('report/ytd_sales_sheet_report/search', { values })
}

const TeamPerformanceReport = {
    search: (values) =>
        requests.post('report/team_performance/search', { values })
}

const UserClone = {
    fetchSections: () =>
        requests.post('user_clone/sections'),
    save: (values, sectionIds, id) =>
        requests.post('user_clone/save', { values, sectionIds, id })
}

const DoctorTown = {
    getTownsByDoctor: (doctor) =>
        requests.post('doctor_town/get_towns_by_doctor', { doctor }),
    save: (doctors, towns) =>
        requests.post('doctor_town/save', { doctors, towns })
}

const RouteChemist = {
    loadChemist: (type, route) =>
        requests.post('route_chemist/' + type + '/load', { route }),
    save: (type, routes, chemists) =>
        requests.post('route_chemist/' + type + '/save', { routes, chemists }),
    loadRoutesByArea: (type, area, keyword) =>
        requests.post('route_chemist/' + type + '/load_routes_by_area', { area, keyword }),
    loadChemistsByArea: (type, area, keyword) =>
        requests.post('route_chemist/' + type + '/load_chemits_by_area', { area, keyword })
}

const SalesItinerary = {
    load: (user, year, month) =>
        requests.post('sales_itinerary/load', { user, year, month }),
    save: (user, year, month, dates) =>
        requests.post('sales_itinerary/save', { user, year, month, dates })
}

const SalesAllocation = {
    search: (mode, searchTerm, page, perPage, additional) =>
        requests.post('sales_allocation/search/' + mode, { searchTerm, page, perPage, additional }),
    load: (team) =>
        requests.post('sales_allocation/load', { team }),
    save: (team, modes, selected, members) =>
        requests.post('sales_allocation/save', { team, modes, selected, members })
}

const InvoiceAllocation = {
    search: (team, terms = {}, page = 1, perPage = 10) =>
        requests.post('invoice_allocation/search', { team, terms, page, perPage }),
    searchProducts: (invoices, keyword, page, perPage) =>
        requests.post('invoice_allocation/search_product', { invoices, keyword, page, perPage }),
    load: (team) =>
        requests.post('invoice_allocation/load', { team }),
    save: (team, mode, selected, productChecked, teamMembers) =>
        requests.post('invoice_allocation/save', { team, mode, selected, productChecked, teamMembers })
}

const SalesItineraryApproval = {
    search: (user, type, area, mode) =>
        requests.post('sales_itinerary_approval/search', { user, type, area, mode }),
    approve: id =>
        requests.post('sales_itinerary_approval/approve', { id })
};

const TownWiseSales = {
    search: (values) =>
        requests.post('town_wise_sales/search', { values }),
    excelSave: (values) =>
        requests.post('town_wise_sales/excel', { values }),
};

const SalesGPS = {
    search: (user, date) =>
        requests.post('sales_gps/search', { user, date })
}

const SalesTarget = {
    load: (rep, month) =>
        requests.post('sr_target/load', { rep, month }),
    save: (rep, products, month) =>
        requests.post('sr_target/save', { rep, products, month })
}

const SalesWeeklyTarget = {
    search: (rep, month) =>
        requests.post('sales_weekly_target/search', { rep, month }),
    save: (targets, rep, month, type) =>
        requests.post('sales_weekly_target/save', { targets, rep, month, type })
}

const SrCustomer = {
    loadCustomer: (sr) =>
        requests.post('sr_customer/load', { sr }),
    save: (srs, customers) =>
        requests.post('sr_customer/save', { srs, customers }),
    loadSrsByArea: (area, keyword) =>
        requests.post('sr_customer/load_srs_by_area', { area, keyword }),
    loadCustomersByArea: (area, keyword) =>
        requests.post('sr_customer/load_customer_by_area', { area, keyword })
}

const ExpensesEdit = {
    search: (rep, month) =>
        requests.post('expenses_edit/search', { rep, month }),
    save: (expenses) =>
        requests.post('expenses_edit/save', { expenses }),
    asm: (values) =>
        requests.post('expenses_edit/asm_save', { values }),
    roll: () =>
        requests.post('expenses_edit/user_roll')
}

const PurchaseOrder = {
    getPurchaseNumber: distributor =>
        requests.post('purchase_order/getNumber', { distributor }),
    getDetails: (product, distributor) =>
        requests.post('purchase_order/getDetails', { product, distributor }),
    save: (distributor, dsr, site, lines) =>
        requests.post('purchase_order/save', { distributor, dsr, site, lines })
}

const SrAllocation = {
    save: (sr, dsr) =>
        requests.post('dsr_allocation/save', { sr, dsr }),
    load: (dsr) =>
        requests.post('dsr_allocation/load', { dsr }),
}

const SiteAllocation = {
    loadSite: (site) =>
        requests.post('site_allocation/load_site', { site }),
    save: (site, dsr) =>
        requests.post('site_allocation/save', { site, dsr }),
    load: (site) =>
        requests.post('site_allocation/load', { site }),
}

const SrProduct = {
    loadProduct: (sr) =>
        requests.post('sr_product/load', { sr }),
    save: (srs, products) =>
        requests.post('sr_product/save', { srs, products }),
    loadSrsByArea: (keyword) =>
        requests.post('panel/user/dropdown', { keyword, where: { u_tp_id: DISTRIBUTOR_SALES_REP_TYPE } }),
    loadProductsByArea: (keyword) =>
        requests.post('panel/product/dropdown', { keyword }),
}

const InvoiceCreation = {
    load: (soNumber) =>
        requests.post('create_invoice/load', { soNumber }),
    save: (soNumber, details, discount, bonusDetails, toApprove = false) =>
        requests.post('create_invoice/save', { soNumber, details, discount, bonusDetails, toApprove }),
    loadBonus: (soNumber, details) =>
        requests.post('create_invoice/load_bonus_scheme', { soNumber, details }),
    loadBatchDetails: (soNumber,product,qty)=>
        requests.post('create_invoice/load_batch_details',{soNumber,product,qty})
}

const SalesmanAllocation = {
    save: (sr, dsr) =>
        requests.post('salesman_allocation/save', { sr, dsr }),
    load: (dsr) =>
        requests.post('salesman_allocation/load', { dsr }),
}

const StockAdjusment = {
    loadAdjNo: (number, dis_id) =>
        requests.post('stock_adjusment/loadAdjNo', { number, dis_id }),
    loadData: (data) =>
        requests.post('stock_adjusment/load_data', { data }),
    saveData: (data, type, dis_id, adjNumber, reason) =>
        requests.post('stock_adjusment/save_data', { data, type, dis_id, adjNumber, reason }),
};

const DirectInvoice = {
    loadLineInfo: (distributor, product, customer) =>
        requests.post('direct_invoice/load_line_info', { distributor, product, customer }),
    save: (distributor, salesman, customer, lines, bonusLines, toApprove) =>
        requests.post('direct_invoice/save', { distributor, salesman, customer, lines, bonusLines, toApprove }),
    loadNumber: (distributor, salesman) =>
        requests.post('direct_invoice/load_next_number', { distributor, salesman }),
    loadBonus: (disId, lines) =>
        requests.post('direct_invoice/load_bonus_scheme', { disId, lines }),
    loadBatchDetails: (distributor,product,qty)=>
        requests.post('direct_invoice/load_batch_details',{distributor,product,qty})
}

const GRNConfirm = {
    fetchProducts: grnNumber =>
        requests.post('grn_confirm/fetch', { grnNumber }),
    save: (id, lines) =>
        requests.post('grn_confirm/save', { id, lines })
}


const CreateReturn = {
    loadLineInfo: (distributor, product) =>
        requests.post('create_return/load_line_info', { distributor, product }),
    save: (distributor, salesman, customer, lines, bonusLines) =>
        requests.post('create_return/save', { distributor, salesman, customer, lines, bonusLines }),
    loadNumber: (distributor, salesman) =>
        requests.post('create_return/load_next_number', { distributor, salesman }),
    loadBonus: (disId, lines) =>
        requests.post('create_return/load_bonus_scheme', { disId, lines })
}

const SalesProcess = {
    submit: (month) =>
        requests.post('sales_process/start', { month }),
    fetchPercentage: () =>
        requests.post('sales_process/checkProgress')
}

const OrderBasedReturn = {
    fetchInvoiceInfo: (invNumber) =>
        requests.post('order_based_return/load_info', { invNumber }),
    fetchBonus: (invNumber, lines) =>
        requests.post('order_based_return/load_bonus', { invNumber, lines }),
    save: (invNumber, lines, bonusLines) =>
        requests.post('order_based_return/save', { invNumber, lines, bonusLines })
};

const BonusApproval = {
    approve: (invId, bonusLines) =>
        requests.post('report/bonus_approval_report/approve', { invId, bonusLines })
}

const PurchaseOrderConfirm = {
    save: (number, lines) =>
        requests.post('purchase_order_confirm/save', { number, lines }),
    load: (number) =>
        requests.post('purchase_order_confirm/load', { number }),
    getDetails: (product, poNumber) =>
        requests.post('purchase_order_confirm/getDetails', { product, poNumber }),
}

const HodGps = {
    search: (user, date) =>
        requests.post('hod_gps_tracking/load', { user, date })
}


const UserTeam = {
    loadTeam: (user) =>
        requests.post('user_team/load', { user }),
    save: (users, teams) =>
        requests.post('user_team/save', { users, teams })
}

const DCAllocation = {
    load: (user) =>
        requests.post('dc_allocation/load', { user }),
    save: (user, chemists) =>
        requests.post('dc_allocation/save', { user, chemists }),
}

const DCItinerary = {
    load: (user, year, month) =>
        requests.post('dc_sales_itinerary/load', { user, year, month }),
    save: (user, year, month, dates) =>
        requests.post('dc_sales_itinerary/save', { user, year, month, dates })
}

const InvoicePayment = {
    load: (number,distributor,salesman,customer) =>
        requests.post('invoice_payment/load', { number,distributor,salesman,customer }),
    save: (customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date) =>
        requests.post('invoice_payment/save', { customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date })
}

const CompanyReturn = {
    load: (grnNumber)=>
        requests.post('company_return/load', {grnNumber}),
    save: (grnNumber, lines, remark)=>
        requests.post('company_return/save',{grnNumber, lines, remark})
}

const Competitor = {
    load: (from,to)=>
        requests.post('competitor/load', {from,to}),
    loadEdit: (id)=>
        requests.post('competitor/loadEdit', {id}),
    Edit: (id,from,to)=>
        requests.post('competitor/edit', {id,from,to}),
}

export default {
    Auth,
    App,
    Crud,
    Itinerary,
    TimeTable,
    TeamProductAllocations,
    Report,
    GPS,
    StandardItinerary,
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
    RouteChemist,
    SalesItinerary,
    SalesAllocation,
    InvoiceAllocation,
    TeamPerformanceReport,
    SalesItineraryApproval,
    TownWiseSales,
    SalesGPS,
    SalesTarget,
    SalesWeeklyTarget,
    SrCustomer,
    ExpensesEdit,
    PurchaseOrder,
    PurchaseOrderConfirm,
    SrAllocation,
    SiteAllocation,
    SrProduct,
    InvoiceCreation,
    SalesmanAllocation,
    StockAdjusment,
    DirectInvoice,
    GRNConfirm,
    CreateReturn,
    SalesProcess,
    OrderBasedReturn,
    BonusApproval,
    HodGps,
    UserTeam,
    DCAllocation,
    DCItinerary,
    InvoicePayment,
    CompanyReturn,
    Competitor
}
