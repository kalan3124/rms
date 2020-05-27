import React,{Component, lazy} from 'react';
import TreeSelect from './TreeSelect';
import AsyncComponent from '../../App/AsyncComponent';
import CloudDownloadIcon from "@material-ui/icons/CloudDownload";
import MultipleAjaxDropdown from './MultipleAjaxDropdown';
import Image from "./Image";
import Button from "./Button";
import Location from "./Location";
import Link from 'react-router-dom/Link';

const customCells = {
    ProductiveDetails:(props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "productive-details" */'./Custom/ProductiveDetails'))}/>),
    Audio:(props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "audio-cell" */'./Custom/Audio'))}/>),
    ItineraryChangeApprove:(props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "approve-button" */'./Custom/ItineraryChangeApprove'))}/>),
    SalesOrderDetails:(props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "sales-order-details" */'./Custom/SalesOrderDetails'))} /> ),
    PurchaseOrderDetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "purchase-order-details" */'./Custom/PurchaseOrderDetails'))} /> ),
    InvoiceDetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "invoice-details" */'./Custom/InvoiceDetails'))} /> ),
    PaymentDetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "payment-details" */'./Custom/PaymentDetails'))} /> ),
    GRNDetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "grn-details" */'./Custom/GRNDetails'))} /> ),
    BonusLines: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "bonus-lines-cell" */'./Custom/BonusLines'))} /> ),
    SWODetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "writeoff-lines-cell" */'./Custom/SWODetails'))} /> ),
    SADetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "adjust-lines-cell" */'./Custom/SADetails'))} /> ),
    BonusApprovalDetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "bonus-approval-details" */'./Custom/BonusApprovalDetails'))} /> ),
    CompanyReturnDetails: (props)=>(<AsyncComponent {...props} RenderModule={lazy(()=>import(/* webpackChunkName: "company-return-details" */'./Custom/CompanyReturnDetails'))} /> ),
}


export default class Cell extends Component{
    render(){
        const {value,type,component,link,label,onDialog} = this.props;
        let children = '';

        if(typeof value=='undefined' || value === null) return null;

        if(type=='custom'){
            let Cmpnt = customCells[component];
            return( <Cmpnt onDialog={onDialog} value={value}/>);
        }

        switch(type){
            case 'ajax_dropdown':
                children = value?value.label:null;
                break;
            case 'select':
                children = value?value.label:null;
                break;
            case 'check':
                children = value=='1'?"YES":"NO";
                break;
            case 'tree_select':
                children = <TreeSelect value={value} />;
                break;
            case 'multiple_ajax_dropdown':
                children = <MultipleAjaxDropdown values={value}  />;
                break;
            case 'file':
                children = <a href={value}><CloudDownloadIcon/></a>;
                break;
            case "image":
                children = <Image value={value}/>
                break;
            case "location":
                children = <Location value={value}/>
                break;
            case 'link':
                if(value){
                    if(value.react)
                        children = <Link to={value.link} >{value.label}</Link>;
                    else
                        children = <a href={value.link} target="_blank">{value.label}</a>;
                } else {
                    children = null;
                }
                break;
            case 'button':
                children = <Button value={value} link={link} label={label} />;
                break;
            case 'number':
                children = <div style={{textAlign:'right'}} >{value}</div>
                break;
            default:
                children = value;
                break;
        }

        return (
            <div>{children}</div>
        )
    }
}
