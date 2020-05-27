import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import rootReducer from './reducers/index';
import createDebounce from 'redux-debounced';
import { createLogger } from 'redux-logger';

const createStoreWithMiddleware = applyMiddleware(
  createDebounce(),
  createLogger(),
  thunk,
)(createStore);


export default createStoreWithMiddleware(rootReducer);
