import React, { Component } from "react";

import withStyles from "@material-ui/core/styles/withStyles";
import ExpansionPanelDetails from "@material-ui/core/ExpansionPanelDetails";
import ExpansionPanelSummary from "@material-ui/core/ExpansionPanelSummary";
import ExpansionPanel from "@material-ui/core/ExpansionPanel";
import ExpandMoreIcon from "@material-ui/icons/ExpandMore";
import Button from "@material-ui/core/Button";
import CheckTable from "./CheckTable";
import SearchField from "./SearchField";
import { Toolbar } from "@material-ui/core";

const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    expansion: {}
}));

class CheckTablePanel extends Component {
    render() {
        const {
            label,
            classes,
            open,
            columns,
            results,
            mode,
            selected,
            onChangeMode,
            onSelect,
            onSubmit,
            onCollapse,
            value,
            onSearch,
            page,
            perPage,
            onChangePage,
            onChangePerPage,
            resultCount
        } = this.props;

        return (
            <ExpansionPanel
                className={classes.expansion}
                onChange={onCollapse}
                expanded={open}
            >
                <ExpansionPanelSummary expandIcon={<ExpandMoreIcon />}>
                    {open ? (
                        <SearchField
                            value={value}
                            onChange={onSearch}
                            label={label}
                        />
                    ) : (
                        label
                    )}
                </ExpansionPanelSummary>
                <ExpansionPanelDetails>
                    <CheckTable
                        columns={columns}
                        results={results}
                        mode={mode}
                        selected={selected}
                        onChangeMode={onChangeMode}
                        onSelect={onSelect}
                        page={page}
                        perPage={perPage}
                        onChangePage={onChangePage}
                        onChangePerPage={onChangePerPage}
                        count={resultCount}
                    />
                    <Toolbar>
                        <div className={classes.grow} />
                        <Button
                            variant="contained"
                            color="primary"
                            onClick={onSubmit}
                        >
                            Next
                        </Button>
                    </Toolbar>
                </ExpansionPanelDetails>
            </ExpansionPanel>
        );
    }
}

export default styler(CheckTablePanel);
