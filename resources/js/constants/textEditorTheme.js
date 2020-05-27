
const colors = {
  almostBlack: "#202020",
  lightBlack: "#2F3336",
  almostWhite: "#E6E6E6",
  white: "#FFF",
  white10: "rgba(255, 255, 255, 0.1)",
  black: "#000",
  black10: "rgba(0, 0, 0, 0.1)",
  primary: "#1AB6FF",
  greyLight: "#F4F7FA",
  grey: "#E8EBED",
  greyMid: "#9BA6B2",
  greyDark: "#DAE1E9",
  transparent:"transparent"
};

export const base = {
  ...colors,
  fontFamily:
    "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen, Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif",
  fontFamilyMono:
    "'SFMono-Regular',Consolas,'Liberation Mono', Menlo, Courier,monospace",
  fontWeight: 100,
  fontSize:'.7em',
  link: colors.primary,
  placeholder: "#B1BECC",
  textSecondary: "#4E5C6E",
  textLight: colors.white,
  selected: colors.primary,
  codeComment: "#6a737d",
  codePunctuation: "#5e6687",
  codeNumber: "#d73a49",
  codeProperty: "#c08b30",
  codeTag: "#3d8fd1",
  codeString: "#032f62",
  codeSelector: "#6679cc",
  codeAttr: "#c76b29",
  codeEntity: "#22a2c9",
  codeKeyword: "#d73a49",
  codeFunction: "#6f42c1",
  codeStatement: "#22a2c9",
  codePlaceholder: "#3d8fd1",
  codeInserted: "#202746",
  codeImportant: "#c94922",
};

export default {
    ...base,
    background: "#eff0f1",
    text: colors.almostBlack,
    code: colors.almostBlack,
  
    toolbarBackground: colors.white,
    toolbarInput: colors.black10,
    toolbarItem: colors.lightBlack,
  
    blockToolbarBackground: colors.transparent,
    blockToolbarTrigger: colors.almostWhite,
    blockToolbarTriggerIcon: colors.almostBlack,
    blockToolbarItem: colors.almostBlack,
  
    quote: colors.almostWhite,
    codeBackground: colors.almostBlack,
    codeBorder: colors.lightBlack,
    codeString: "#3d8fd1",
    horizontalRule: colors.almostWhite,
    imageErrorBackground: "rgba(0, 0, 0, 0.5)",
  };