import React, { Component } from "react";
import { connect } from "react-redux";
import Layout from "../App/Layout";
import {
    fetchInformation,
    formValuesChange,
    fetchResults,
    changeSort,
    clearForm,
    createRecord,
    chooseToUpdate,
    updateRecord,
    clickCreate,
    clickSearch,
    changePageNumber,
    changeRowCount,
    clickDelete,
    confirmDelete,
    clearDelete,
    download,
    clickRestore,
    clearRestore,
    confirmRestore
} from "../../actions/CrudPage";
import CrudForm from "./CrudForm";

import withStyles from "@material-ui/core/styles/withStyles";
import Grid from "@material-ui/core/Grid";
import Modal from "@material-ui/core/Modal";
import Paper from "@material-ui/core/Paper";
import Divider from "@material-ui/core/Divider";

import CrudTable from "./CrudTable";
import TopPanel from "./TopPanel";
import BottomPanel from "./BottomPanel";

const mapStateToProps = state => ({
    ...state,
    ...state.CrudPage,
    ...state.Layout,
    ...state.Sidebar
});

const styles = theme => ({
    backdrop: {
        right: 24,
        background: "unset"
    },
    margin: {
        margin: theme.spacing.unit
    },
    layout: {
        paddingRight: theme.spacing.unit * 4,
        paddingLeft: theme.spacing.unit * 24
    },
    modal: {
        backgroundColor: "rgba(0, 0, 0, 0.5)",
        paddingBottom: 40,
        overflow: "auto"
    }
});

class CrudPage extends Component {
    componentDidMount() {
        const { match } = this.props;

        this.handleChangePage(match.params.form);

        if (match.params.mode == "create") {
            this.handleCreateClick();
        }
    }

    componentWillReceiveProps(nextProps) {
        const { match } = this.props;

        if (match.params.form != nextProps.match.params.form) {
            this.handleChangePage(nextProps.match.params.form);
        }

        if (match.params.mode != nextProps.match.params.mode) {
            if (nextProps.match.params.mode == "create") {
                this.handleCreateClick();
            }
        }
    }

    handleChangePage(page) {
        const { dispatch } = this.props;

        dispatch(fetchInformation(page));
    }

    handleChangeForm(values) {
        const { dispatch } = this.props;

        dispatch(formValuesChange(values));
    }

    handleSearch(values) {
        const { dispatch, match } = this.props;

        dispatch(
            fetchResults(
                match.params.form,
                ...this.getSearchParameters({ values }),
                true
            )
        );
    }

    handleCSVDownload() {
        const { dispatch, match } = this.props;

        dispatch(
            download("csv", match.params.form, ...this.getSearchParameters())
        );
    }

    handlePDFDownload() {
        const { dispatch, match } = this.props;

        dispatch(
            download("pdf", match.params.form, ...this.getSearchParameters())
        );
    }

    handleXLSXDownload() {
        const { dispatch, match } = this.props;

        dispatch(
            download("xlsx", match.params.form, ...this.getSearchParameters())
        );
    }

    handleCSVUpload() {
        const { match, history } = this.props;
        const formName = match.params.form;
        history.push("/medical/other/upload_csv/form/" + formName);
    }

    handleSortChange(column, mode) {
        const { dispatch, match } = this.props;

        dispatch(
            changeSort(
                match.params.form,
                ...this.getSearchParameters({ sortBy: column, sortMode: mode })
            )
        );
    }

    getSearchParameters(newValues = {}) {
        const {
            lastValues,
            sortBy,
            sortMode,
            page,
            perPage,
            searching,
            popupMode
        } = this.props;

        const oldValues = {
            values: searching ? lastValues : ( typeof popupMode == 'undefined'|| popupMode=="update"||popupMode=="create"?lastValues: {}),
            sortBy,
            sortMode,
            page,
            perPage
        };

        const merged = { ...oldValues, ...newValues };

        return [
            merged.values,
            merged.sortBy,
            merged.sortMode,
            merged.page,
            merged.perPage
        ];
    }

    handleSubmit() {
        const { updatingId, dispatch, match } = this.props;
        if (updatingId)
            dispatch(
                updateRecord(
                    match.params.form,
                    updatingId,
                    ...this.getSearchParameters()
                )
            );
        else
            dispatch(
                createRecord(match.params.form, ...this.getSearchParameters())
            );
    }

    handleClear() {
        this.props.dispatch(clearForm());
    }

    handleChooseForUpdate(i) {
        const { dispatch, results, inputs } = this.props;

        let values = {};

        Object.keys(inputs).forEach(name => {
            values[name] = results[i][name];
        });

        dispatch(chooseToUpdate(results[i].id, values));
        dispatch(formValuesChange(values));
    }

    handleSearchClick() {
        const { dispatch } = this.props;

        dispatch(clickSearch());
    }

    handleCreateClick() {
        const { dispatch } = this.props;

        dispatch(clickCreate());
    }

    handleConfirmDelete() {
        const { match, dispatch, updatingId } = this.props;

        dispatch(
            confirmDelete(
                match.params.form,
                updatingId,
                ...this.getSearchParameters()
            )
        );
    }

    handleCancelDelete() {
        const { dispatch } = this.props;

        dispatch(clearDelete());
    }

    handleClickDelete(r) {
        const { results, dispatch } = this.props;

        dispatch(
            clickDelete(
                results[r].id,
                this.handleConfirmDelete.bind(this),
                this.handleCancelDelete.bind(this)
            )
        );
    }

    handleConfirmRestore() {
        const { match, dispatch, updatingId } = this.props;
        dispatch(
            confirmRestore(
                match.params.form,
                updatingId,
                ...this.getSearchParameters()
            )
        );
    }

    handleCancelRestore() {
        const { dispatch } = this.props;

        dispatch(clearRestore());
    }

    handleClickRestore(r) {
        const { results, dispatch } = this.props;
        dispatch(
            clickRestore(
                results[r].id,
                this.handleConfirmRestore.bind(this),
                this.handleCancelRestore.bind(this)
            )
        );
    }

    handleChangePageNumber(e, page) {
        const { dispatch, match } = this.props;
        if (!Boolean(e) || !Boolean(e.target)) return;

        dispatch(
            changePageNumber(
                match.params.form,
                ...this.getSearchParameters({ page: page + 1 })
            )
        );
    }

    handleChangeRowCount(e) {
        const { dispatch, match } = this.props;
        if (!Boolean(e) || !Boolean(e.target)) return;

        dispatch(
            changeRowCount(
                match.params.form,
                ...this.getSearchParameters({ perPage: e.target.value })
            )
        );
    }

    renderTable() {
        const {
            columns,
            sortBy,
            sortMode,
            results,
            privilegedActions
        } = this.props;

        return (
            <CrudTable
                columns={columns}
                sortBy={sortBy}
                sortMode={sortMode}
                onSortChange={this.handleSortChange.bind(this)}
                results={results}
                actions={privilegedActions.filter(
                    actionName => actionName != "create"
                )}
                onUpdate={this.handleChooseForUpdate.bind(this)}
                onDelete={this.handleClickDelete.bind(this)}
                onRestore={this.handleClickRestore.bind(this)}
            />
        );
    }

    handleModalClose() {
        const { dispatch } = this.props;

        dispatch(clearForm());
    }

    render() {
        const {
            title,
            inputs,
            updatingId,
            structure,
            values,
            classes,
            searching,
            popupMode,
            page,
            perPage,
            resultCount,
            match,
            privilegedActions
        } = this.props;

        return (
            <Layout sidebar>
                <Modal
                    aria-labelledby="simple-modal-title"
                    aria-describedby="simple-modal-description"
                    open={typeof popupMode != "undefined"}
                    onClose={this.handleModalClose.bind(this)}
                    BackdropProps={{
                        className: classes.backdrop
                    }}
                    className={classes.modal}
                >
                    <div style={{display:"inline"}}>
                        <CrudForm
                            title={title}
                            inputs={inputs}
                            update={updatingId}
                            structure={structure}
                            onChange={this.handleChangeForm.bind(this)}
                            values={values}
                            onInputChange={this.handleChangeForm.bind(this)}
                            onSearch={this.handleSearch.bind(this)}
                            onSubmit={this.handleSubmit.bind(this)}
                            onClear={this.handleClear.bind(this)}
                            mode={popupMode}
                            searching={searching}
                        />
                    </div>
                </Modal>
                <Grid container>
                    <Grid item md={12}>
                        <TopPanel
                            create={privilegedActions.includes("create")}
                            onSearchClik={this.handleSearchClick.bind(this)}
                            onCreateClick={this.handleCreateClick.bind(this)}
                            title={title}
                        />
                        <Divider />
                        {this.renderTable()}
                        <BottomPanel
                            onChangePage={this.handleChangePageNumber.bind(
                                this
                            )}
                            onChangeRowCount={this.handleChangeRowCount.bind(
                                this
                            )}
                            page={page - 1}
                            perPage={perPage}
                            resultCount={resultCount}
                            onDownloadCSV={this.handleCSVDownload.bind(this)}
                            onDownloadPDF={this.handlePDFDownload.bind(this)}
                            onDownloadXLSX={this.handleXLSXDownload.bind(this)}
                            onUploadCSV={this.handleCSVUpload.bind(this)}
                            formName={match.params.form}
                            disableUpload={
                                !privilegedActions.includes("create")
                            }
                        />
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

export default withStyles(styles)(connect(mapStateToProps)(CrudPage));
