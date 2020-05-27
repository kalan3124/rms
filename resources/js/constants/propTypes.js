import PropTypes from "prop-types";

export const PropNumOrString = PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number
]);

export const PropDropdownOption = PropTypes.shape({
    label: PropTypes.string,
    value: PropNumOrString
});