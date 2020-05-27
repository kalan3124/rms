import { MR_PS_REPORT_LOAD_VALUES,MR_PS_REPORT_LOAD_RESULT} from "../../constants/actionTypes";

const initialState = {
     values:{},
     searched:false,
     rowData:[]
}

export default (state=initialState,{payload,type})=>{
     switch (type) {
          case MR_PS_REPORT_LOAD_VALUES:
          return {
              ...state,
              values:{
                  ...state.values,
                  [payload.name]:payload.value
              }
          };
          case MR_PS_REPORT_LOAD_RESULT:
            return {
                ...state,
                rowData:payload.results,
                searched:true,
                resultCount:payload.count
            };
          default:
            return state;
     }
}