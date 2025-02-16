import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

// Register Gutenberg block
registerBlockType("nws-alerts-plugin/nws-alerts-block", {
  title: __("NWS Alerts Block", "nws-alerts-plugin"),
  icon: "warning",
  category: "widgets",
  edit: () => {
    return (
      <p
        {...useBlockProps()}
        style={{ padding: "10px", backgroundColor: "#ffeb3b", color: "#333" }}
      >
        {__(
          "NWS Alerts will be displayed here on the frontend.",
          "nws-alerts-plugin"
        )}
      </p>
    );
  },
  save: () => {
    return <div {...useBlockProps()} id="nws-alerts-plugin-container"></div>;
  },
});
