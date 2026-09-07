# Phase IV.35.8B — Every Dungeon Needs a Door

Dungeon Forge may now optionally create a **Main entrance** or **Arrival portal**.

A main dungeon entrance is cut into a deterministic exterior boundary and becomes a real Forge door rather than painted decoration. A portal chooses a playable interior arrival point. Outdoor scenes use a deterministic edge arrival for Entrance and an interior point for Portal.

Both modes persist an `entry_anchor` in the Forge projection and reuse the existing **Party Arrival Threshold** system. Building a Forge draft with a way in replaces only existing Party Arrival markers for that Scene; Monster Deployment markers remain untouched. Players therefore arrive through the same certified Threshold/token flow already used by the Keeper's Atlas.

The default remains **None**, so existing generation behaviour is unchanged unless the Keeper opts in.

> Pippin: “I have added a way in.”
>
> Keeper: “And a way out?”
>
> Pippin: “The door is reversible.”
